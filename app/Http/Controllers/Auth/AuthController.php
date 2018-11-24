<?php

namespace App\Http\Controllers\Auth;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;

use App\User;
use App\Customer;


class AuthController extends Controller
{      

    /**
     * @SWG\Post(
     *   path="/customer/login",
     *   tags={"Buyer Module"},
     *   summary="Customer Login",
     *        @SWG\Parameter(
     *         name="customer",
     *         in="body",
     *         description="Customer Details [CustomerObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email",type="string", description="Email Address"),
     *              @SWG\Property(property="password",type="string", description="Password or PIN"),
     *         )
     *     ),
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: customer_authenicated, data: [CustomerObject]}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * @SWG\Post(
     *   path="/seller/login",
     *   tags={"Seller Module"},
     *   summary="Seller Login",
     *        @SWG\Parameter(
     *         name="seller",
     *         in="body",
     *         description="Seller Details [SellerObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email",type="string", description="Email Address"),
     *              @SWG\Property(property="password",type="string", description="Password or PIN"),
     *         )
     *     ),
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: seller_authenicated, data: [SellerObject]}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * @SWG\Post(
     *   path="/deliverymen/login",
     *   tags={"DeliveryMan Module"},
     *   summary="Delivery Man Login",
     *        @SWG\Parameter(
     *         name="deliverymen",
     *         in="body",
     *         description="Delivery Man Details [SellerObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email",type="string", description="Email Address"),
     *              @SWG\Property(property="password",type="string", description="Password or PIN"),
     *         )
     *     ),
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: deliveryman_authenicated, data: [SellerObject]}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * Handle users or customer login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @param $role from url (GET)
     *
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request, $role)
    {
        

        try {
            $this->validate($request, Customer::loginValidation());
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        try {
            //set user to customer
            if($role === 'customer'){
                 Config::set('auth.providers.user.model', \App\Customer::class);
            }

            // Attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt(
               $this->getCredentials($request)
            )) {
                return $this->onUnauthorized('invalid_credentials');
            }

        } catch (JWTException $e) {
            // Something went wrong whilst attempting to encode the token
            return $this->onJwtGenerationError();
        }

        // All good so return the token
        return $this->onAuthorized($token);
    }

    /**
     * What response should be returned on invalid credentials.
     *
     * @return JsonResponse
     */
    protected function onUnauthorized($msg)
    {
        return new JsonResponse([
            'status' => 'error',
            'message' => $msg
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * What response should be returned on error while generate JWT.
     *
     * @return JsonResponse
     */
    protected function onJwtGenerationError()
    {
        return new JsonResponse([
            'status' => 'error',
            'message' => 'could_not_create_token'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * What response should be returned on authorized.
     *
     * @return JsonResponse
     */
    protected function onAuthorized($token)
    {
        return new JsonResponse([
            'status' => 'success',
            'message' => 'login_successful',
            'data' => [
                'token' => $token,
            ]
        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function getCredentials(Request $request)
    {
        return $request->only('email', 'password');
    }

    /**
     * Invalidate a token.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteInvalidate()
    {
        $token = JWTAuth::parseToken();

        $token->invalidate();

        return new JsonResponse(['message' => 'token_invalidated']);
    }
    

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\Response
     */
    public function patchRefresh()
    {
        $token = JWTAuth::parseToken();

        $newToken = $token->refresh();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'token_refreshed',
            'data' => [
                'token' => $newToken
            ]
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/seller/changePassword",
     *   tags={"Seller Module"},
     *   summary="Change Seller Password",
     *        @SWG\Parameter(
     *         name="seller",
     *         in="body",
     *         description="Change Seller password",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="current_password",type="string", description="Seller Current Password"),
     *              @SWG\Property(property="new_password",type="string", description="Seller New Password"),
     *              @SWG\Property(property="new_password_confirmation",type="string", description="Seller New Password Confirmation")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200, 
     *      description="{status: success, message: password_updated]}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: parameters missing or required/record_not_found/password_does_not_match}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]/role_not_defined}"),
     
     * )
     * 
     * 
     *
     * 
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @SWG\Post(
     *   path="/customer/changePassword",
     *   tags={"Buyer Module"},
     *   summary="Change Customer Password",
     *        @SWG\Parameter(
     *         name="customer",
     *         in="body",
     *         description="Change customer password",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="current_password",type="string", description="Customer Current Password"),
     *              @SWG\Property(property="new_password",type="string", description="Customer New Password"),
     *              @SWG\Property(property="new_password_confirmation",type="string", description="Customer New Password Confirmation")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200, 
     *      description="{status: success, message: password_updated]}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: parameters missing or required/record_not_found/password_does_not_match}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]/role_not_defined}"),
     * )
     * 
     * 
     *
     * 
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //update password

     /**
     * @SWG\Post(
     *   path="/deliveryman/changePassword",
     *   tags={"DeliveryMan Module"},
     *   summary="Change Delivery Man Password",
     *        @SWG\Parameter(
     *         name="customer",
     *         in="body",
     *         description="Change deliveryman password",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="current_password",type="string", description="deliveryman Current Password"),
     *              @SWG\Property(property="new_password",type="string", description="deliveryman New Password"),
     *              @SWG\Property(property="new_password_confirmation",type="string", description="deliveryman New Password Confirmation")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200, 
     *      description="{status: success, message: password_updated]}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: parameters missing or required/record_not_found/password_does_not_match}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]/role_not_defined/unauthorized_access}"),
     * )
     * 
     * 
     *
     * 
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //update password

    public function changePassword (Request $request, $role) {

        $id = JWTAuth::parseToken()->authenticate()->id;
        $hasher = app()->make('hash');
        $user = null;

        
        if($role == "seller" || $role == "deliveryman"){
            try {
                //validate request parameters
                $validation = User::getPasswordValidation();
                $this->validate($request, $validation);

            } catch (ValidationException $e) {
                //get Missing Parameters and fit them into message returning as status 201
                return $this->dataResponse($this->getMissingParams($e), null, 'error');

            }
            $user = User::find($id);

        }else if($role == "customer"){
            //validate request parameters for customer role
            try {

                $validation = Customer::getPasswordValidation();
                $this->validate($request, $validation);

            } catch (ValidationException $e) {
                //get Missing Parameters and fit them into message returning as status 201
                return $this->dataResponse($this->getMissingParams($e), null, 'error');

            }

            $user = Customer::find($id);

        }else{
            return $this->onUnauthorized('role_not_defined');
        }
        

        if(!$user)
            return $this->dataResponse('record_not_found', null, 'error');

        if(!$hasher->check($request->input('current_password'), $user->password))
            return $this->dataResponse('password_does_not_match', null, 'error');

            $user->password = $hasher->make($request->input('new_password'));
            $user->save();
            return $this->dataResponse('password_updated', null);
    }


    /**
     * Get authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUser($role)
    {
        return new JsonResponse([
            'status' => 'success',
            'message' => 'authenticated_user',
            'data' => JWTAuth::parseToken()->authenticate()
        ]);
    }

    /**
     * 
     * 
     * The Below Contains Utility functions in carrying out repeated tasks 
     * dataResponse for options config on endpoint Response
     * 
     * getMissingParams for extracting validation messages
     * getUserId for retrieving ID of authenticated user
     */
    //to return response in each method

    private function getMissingParams (ValidationException $e) {
       
        $message = "";
        foreach($e->getResponse()->getOriginalContent() as $key=>$value) {
            $message = $message . $value[0].'\n';
        }
        return $message;
    }

    private function dataResponse ($message, $data = null, $status = "success") {
        if($status == "error")
            $statusCode = Response::HTTP_CREATED;
        else 
            $statusCode = Response::HTTP_OK;

        if($data == null){
            return new JsonResponse([
                'status' => $status,
                'message' => $message
            ], $statusCode);
        }

        return new JsonResponse([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
