<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

use App\User;
use App\Customer;


class ForgotPasswordController extends Controller
{
	/**
     * @SWG\Post(
     *   path="/customer/forgot",
     *   tags={"Buyer Module"},
     *   summary="Customer Forgot Password",
     *        @SWG\Parameter(
     *         name="customer",
     *         in="body",
     *         description="Customer email to get email with reset token",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email",type="string", description="Email Address")
     *         )
     *     ),
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: token_generated, data: [token: reset_token_key ]}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: user_not_found}"),
     * )
    /**
    /**
     * @SWG\Post(
     *   path="/seller/forgot",
     *   tags={"Seller Module"},
     *   summary="Seller Forgot Password",
     *        @SWG\Parameter(
     *         name="seller",
     *         in="body",
     *         description="Seller email to get email with reset token",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email",type="string", description="Email Address"),
     *         )
     *     ),
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: token_generated, data: [token: reset_token_key ]}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: user_not_found}"),
     * )
     * )
    /**
     /**
     * @SWG\Post(
     *   path="/deliverymen/forgot",
     *   tags={"DeliveryMan Module"},
     *   summary="Delivery Man Login",
     *        @SWG\Parameter(
     *         name="deliverymen",
     *         in="body",
     *         description="Deliveryman email to get email with reset token",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email",type="string", description="Email Address"),
     *         )
     *     ),
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: token_generated, data: [token: reset_token_key ]}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: user_not_found}"),
     * )
     */

    public function getResetToken(Request $request, $role)
    {

        try {
            $this->validate($request, User::getResetValidationRules());
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $user = User::where('email', $request->input('email'))->first();
        $broker = 'user';

        //set user to customer
        if($role === 'customer'){
             Config::set('auth.providers.user.model', \App\Customer::class);
             $user = Customer::where('email', $request->input('email'))->first();
             $broker = 'customer';
        }

        if (!$user) {
        	return $this->dataResponse('user_not_found', null, 'error');
        }

        $token = Password::broker($broker)->createToken($user);

        return $this->dataResponse('token_generated', ['token' => $token]);
    }

    /**
     * @SWG\Post(
     *   path="/customer/forgot/reset",
     *   tags={"Buyer Module"},
     *   summary="Customer Reset Forgot Password",
     *        @SWG\Parameter(
     *         name="customer",
     *         in="body",
     *         description="Customer email, new password and reset token to reset password",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email",type="string", description="Email Address"),
     *              @SWG\Property(property="password", type="string", description="New Password"),
     *              @SWG\Property(property="password_confirmation",type="string", description="Confirm New Password"),
     *              @SWG\Property(property="token",type="string", description="Reset Token from email/forgot response")
     *         )
     *     ),
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: reset_successful}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: reset_failed}"),
     * )
    /**
    /**
     * @SWG\Post(
     *   path="/seller/forgot/reset",
     *   tags={"Seller Module"},
     *   summary="Seller Reset Forgot Password",
     *        @SWG\Parameter(
     *         name="seller",
     *         in="body",
     *         description="Seller email, new password and reset token to reset password",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email",type="string", description="Email Address"),
     *              @SWG\Property(property="password",type="string", description="New Password"),
     *              @SWG\Property(property="password_confirmation",type="string", description="Confirm New Password"),
     *              @SWG\Property(property="token",type="string", description="Reset Token from email/forgot response")
     *         )
     *     ),
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: reset_successful}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: reset_failed"),
     * )
    /**
     /**
     * @SWG\Post(
     *   path="/deliverymen/forgot/reset",
     *   tags={"DeliveryMan Module"},
     *   summary="Delivery Man Reset Forgot Password",
     *        @SWG\Parameter(
     *         name="deliverymen",
     *         in="body",
     *         description="Deliveryman email, new password and reset token to reset password",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email",type="string", description="Email Address"),
     *              @SWG\Property(property="password",type="string", description="New Password"),
     *              @SWG\Property(property="password_confirmation",type="string", description="Confirm New Password"),
     *              @SWG\Property(property="token",type="string", description="Reset Token from email/forgot response")
     *         )
     *     ),
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: reset_successful}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: reset_failed}"),
     * )
     */


    public function reset(Request $request, $role)
    {

        try {
            $this->validate($request, User::getResetInputsValidationRules());
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

         $broker = 'user';


        //set user to customer
        if($role === 'customer'){
             Config::set('auth.providers.user.model', \App\Customer::class);
             $user = Customer::where('email', $request->input('email'))->first();
             $broker = 'customer';
        }

        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $response = Password::broker($broker)->reset($credentials, function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        
        if ($response) {
            return $this->dataResponse('reset_successful', null);
        } else {
            return $this->dataResponse('reset_failed', null, 'error');
        }
        
        //send Mail
        // return $response == Password::PASSWORD_RESET
        // ? $this->sendResetResponse($response)
        // : $this->sendResetFailedResponse($request, $response);
    }



    //$this methods
     protected function resetPassword($user, $password)
    {
    	$hasher = app()->make('hash');
        $user->password = $hasher->make($password);
        $user->save();

        return true;
    }


    private function getMissingParams (ValidationException $e) {
	    $message = "";
	    foreach($e->getResponse()->getOriginalContent() as $key=>$value) {
	        $message = $message . $value[0].'\n';
	    }
	    return $message;
	}

    //to return response in each method
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
