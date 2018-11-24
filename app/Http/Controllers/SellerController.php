<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\SellerConfirmationMail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;

use App\Library\Verified\Verified;

use App\User as User;
use App\Customer as Customer;
use App\Transaction as Transaction;
use App\RefundRequest as RefundRequest;
use App\Seller as Seller;
use App\DeliveryTerm as DeliveryTerm;
use App\DeliveryMan as DeliveryMan;
use App\Account as Account;

use App\Library\Utils;
use \MAbiola\Paystack\Paystack as PayStack;


class SellerController extends Controller
{
    use Utils;

/**
 * @SWG\Post(
 *   path="/seller/register",
 *   tags={"Seller Module"},
 *   summary="Create New Seller",
 *        @SWG\Parameter(
 *         name="seller",
 *         in="body",
 *         description="Seller Details [SellerObject]",
 *         required=true,
 *         @SWG\Schema(
 *             type="object",
 *              @SWG\Property(property="firstname",type="string", description="Seller Firstname"),
 *              @SWG\Property(property="lastname",type="string", description="Seller Lastname"),
 *              @SWG\Property(property="phone_number",type="string", description="Seller Phone Number"),
 *              @SWG\Property(property="email",type="string", description="Seller Email Address"),
 *              @SWG\Property(property="password",type="string", description="Seller Password"),
 *              @SWG\Property(property="password_confirmation",type="string", description="Seller Password Confirmation"),
 *              @SWG\Property(property="PIN",type="integer", description="Seller's PIN"),
 *              @SWG\Property(property="PIN_confirmation",type="integer", description="Seller's PIN Confirmation"),
 *              @SWG\Property(property="ip_address",type="string", description="Customer IP Address"),
 *              @SWG\Property(property="device_type",type="string", description="Customer Device Type"),
 *              @SWG\Property(property="device_version",type="string", description="Customer Device Version"),
 *         )
 *     ),
 *  
 *   @SWG\Response(
 *      response=200,
 *      description="{status=success, message=customer_registered}"),
 *  @SWG\Response(
 *      response=201,
 *      description="{status=error, message=errorText}"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
 * )
 * 
 * 
 *
 * Create a Customer Record
 *
 * @return \Illuminate\Http\Response
 */


    public function register (Request $request) {
        //dependent on Authentication Module

        try {
            $this->validate($request, User::getValidationRules());
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $hasher = app()->make('hash');
        $code = hash_hmac('sha256', str_random(40), config('app.key'));


        ///Bvn Validation

            // $firstname = $request->input('firstname');
            // $bvn = $request->input('BVN');

            // $data = [
            //     'firstname' => $firstname,
            //     'bvn' => $bvn
            // ];
            // $verify = new Verified();
            // $response = $verify->verify_single($data);
            
            // $response_array = json_decode($response, true);
            
            // if ('success' === $response_array['status']) {
            //     $user_verified = 1;
            // }

            // else {
            //     $user_verified = 0;

            //     if ('Failed, Network Error' == $response_array['data']['message'] || 'SERVICE_UNAVAILABLE' == $response_array['data']['status']) {
            //         return $this->dataResponse('Bvn Validation Service Unavailable', $response, 'error');
            //     }

            //     return $this->dataResponse('Bvn details Unmatched', $response, 'error');
            // }

        // End of Bvn Validation
        $user_verified = 0;

        $user = ([
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'phone_number' => $request->input('phone_number'),
            'email' => $request->input('email'),
            'password' => $hasher->make($request->input('password')),
            'PIN' => $hasher->make($request->input('PIN')),
            'verified' => $user_verified,
            'ip_address' => $request->input('ip_address'),
            'device_type' => $request->input('device_type'),
            'device_version' => $request->input('device_version'),
            'confirmation_token' => $code,
        ]);

        // $seller->save();
        // $response = $seller->users()->save($user);
        $response = User::create($user);

        Mail::to($request->input('email'))->send(new SellerConfirmationMail($request->input('firstname'), $request->input('email'), $request->input('phone_number'), $code, env("LIVE_URL")));
        // if($user_verified == 0)
        //     return $this->dataResponse('seller_created, Bvn was not verified', $response);

        return $this->dataResponse('seller_created', $response);
      
    }

/**
 * @SWG\Get(
 *   path="/seller/confirmation/{token}",
 *   tags={"Seller Module"},
 *   summary="Seller Email Confirmation",
 *        @SWG\Parameter(
 *         name="token",
 *         in="query",
 *         type="string",
 *         description="Token from mail sent to seller",
 *         required=true,
 *         @SWG\Items(type="string")
 *     ),
 *   @SWG\Response(
 *      response=200,
 *      description="{status: success, message: seller_verified}"),
 * @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: invalid_token}"),
 * )
 * Handle customer registration token for comfirmation.
 *
 * 
 * @param $token from url
 *
 * @return \Illuminate\Http\Response
 */
    public function confirmSeller ($token) {

        $seller= User::where('confirmation_token', $token)
            ->where('status', 0)->first();

        if($seller){
            
            // if($seller->verified != 1){
            //     return $this->dataResponse('bvn not verified', null, 'error');
            // }

            if($seller->confirmation_token == $token){
                $seller->status = 1;

                if($seller->save()){
                    return $this->dataResponse('seller_verified');
                }
            }
        } 

        return $this->dataResponse('invalid_token', null, 'error'); 
    }

/**
 * @SWG\Get(
 *   path="/seller/account",
 *   tags={"Seller Module"},
 *   summary="Get Authenticated Seller Account Details",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: seller_details, data: [sellerObject]}"),
 *    @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: seller_not_found}"),
    * @SWG\Response(
    *      response=401, 
    *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
    * )
 * )
 *  
 *
 * Display a seller Details.
 *
 * @return \Illuminate\Http\Response
 */
    
    //view seller details
    public function getSellerAccountDetails (Request $request) {   
        
        $user_id = $this->getUserId();
        $seller = User::where('id', $user_id)->first();
        if($seller) {
            return $this->dataResponse('seller_details', $seller);
        }

        return $this->dataResponse('seller_not_found', null, 'error');
    }

    
/**
 * @SWG\Get(
 *   path="/seller",
 *   tags={"Seller Module"},
 *   summary="Get Authenticated Seller Details",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: seller_details, data: [sellerObject]}"),
 *    @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: seller_not_found}"),
    * @SWG\Response(
    *      response=401, 
    *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
    * )
 * )
 *  
 *
 * Display a seller Details.
 *
 * @return \Illuminate\Http\Response
 */
    
    //view seller details
    public function getSellerDetails (Request $request) {   
        
        $sellerId = $this->getUserId();
        $seller = User::with('seller')->where('id', $sellerId)->first();
        if($seller) {
            return $this->dataResponse('seller_details', $seller);
        }

        return $this->dataResponse('seller_not_found', null, 'error');
    }

     /**
     * @SWG\Put(
     *   path="/seller/business",
    *   tags={"Seller Module"},
     *   summary="Add Seller Business Information",
     *        @SWG\Parameter(
     *         name="seller",
     *         in="body",
     *         description="Seller Details [SellerObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="is_marketplace",type="integer", description="1 or 0 Is Seller A MarketPlace Admin ?"),
     *              @SWG\Property(property="marketplace_child",type="string", description="MarketPlace Child/ Parent to"),
     *              @SWG\Property(property="business_name",type="string", format="varchar255", description="Business Name"),
     *              @SWG\Property(property="business_address",type="string", format="varchar255", description="Business Address"),
     *              @SWG\Property(property="business_address_2",type="string", format="varchar255", description="Business Address 2"),
     *              @SWG\Property(property="business_city",type="string", format="varchar255", description="Business City"),
     *              @SWG\Property(property="business_state",type="string", format="varchar255", description="Business State"),
     *              @SWG\Property(property="business_country",type="string", format="varchar255", description="Business State"),
     *              @SWG\Property(property="business_email",type="string", format="varchar255", description="Business Email"),
     *              @SWG\Property(property="siteURL",type="string", format="varchar255", description="Site URL"),
     *              @SWG\Property(property="BVN", type="string", format="varchar255", description="Bank Verification Number")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200, 
     *      description="{status: success, message: updated_successfully, data: [sellerObject]}"),
     *  @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: Some Missing Parameters are required / seller_details_not_found/update_failed }"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     
     * )
     * Update a Seller's Details
     *
     * @return \Illuminate\Http\Response
     */




    //Add Seller Business Information 
    public function update_business (Request $request) {

        try {
            $this->validate($request, Seller::getValidationRule());
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }
        
        //Get the current User

        $user_id = $this->getUserId();
        $user = User::find($user_id);
        if(!$user) {
            return $this->dataResponse('user_not_found', null, 'error');
        }

        $firstname = $user->firstname;
        $bvn = $request->input('BVN');

        $data = [
            'firstname' => $firstname,
            'bvn' => $bvn
        ];

        $verify = new Verified();
        $response = $verify->verify_single($data);
        
        $response_array = json_decode($response, true);
        
        if ('success' === $response_array['status']) {
            $user->verified = 1;
            $user->bvn = Hash::make($request->input('BVN'));
            $user->save();
        }
        else {

            if ('Failed, Network Error' == $response_array['message'] || 'SERVICE_UNAVAILABLE' == $response_array['data']['status']) {
                return new JsonResponse($response_array, Response::HTTP_CREATED);
            }

            return $this->dataResponse('bvn details unmatched', $response_array, 'error');
        }

        $seller = Seller::create([
            'is_marketplace' => $request->input('is_marketplace'),
            'marketplace_child' => $request->input('marketplace_child'),
            'business_name' => $request->input('business_name'),
            'business_address' => $request->input('business_address'),
            'business_address_2' => $request->input('business_address_2'),
            'business_city' => $request->input('business_city'),
            'business_state' => $request->input('business_state'),
            'business_country' => $request->input('business_country'),
            'business_email' => $request->input('business_email'),
            'siteURL' => $request->input('siteURL')
        ]);

        // $response = $seller->save();
        $response = $seller->users()->save($user);
        if($response) {
            return $this->dataResponse('added_business_info', $seller);
        }

        return $this->dataResponse('business_info_not_added', null, 'error');

        // //get all edited fields
        // $updateFields = [];
        // $validationArray = [];
        // foreach ($seller->getFillable() as $value=>$key) {

        //     if ($request[$key] !== $seller[$key]){
        //         $updateFields[$key] = $request[$key];
        //         $validationArray[$key] = $seller->getValidationRule()[$key];
        //     }
        // }

        // try {
        //     $this->validate($request, $validationArray);
        // } catch (ValidationException $e) {
        //     return $this->dataResponse($this->getMissingParams($e), null, 'error');
        // }

        // $res = Seller::where('id', $sellerId)->update($updateFields);
        // if($res > 0)
        //     return $this->dataResponse('update_successful', Seller::where('id', $sellerId)->first());
        
        // return $this->dataResponse('update_failed', null, 'error');
    }

     /**
     * @SWG\Put(
     *   path="/seller/account/update",
    *   tags={"Seller Module"},
     *   summary="Update/Edit Seller Account Details",
     *        @SWG\Parameter(
     *         name="seller",
     *         in="body",
     *         description="Seller Details [SellerObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="firstname",type="string", description="Seller Firstname"),
 *              @SWG\Property(property="lastname",type="string", description="Seller Lastname"),
 *              @SWG\Property(property="phone",type="string", description="Seller Phone Number"),
 *              @SWG\Property(property="email",type="string", description="Seller Email Address"),
     *             
     * 
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200, 
     *      description="{status: success, message: updated_successfully, data: [sellerObject]}"),
     *  @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: Some Missing Parameters are required / seller_details_not_found/update_failed }"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     
     * )
     * Update a Seller's Details
     *
     * @return \Illuminate\Http\Response
     */




    //update authenticated seller Details excluding sensitive details
    public function updateSellerAccount (Request $request) {
        $validate = [
                'firstname' => 'required|min:4',
                'lastname' => 'required|min:4',
                'phone_number' => 'required|min:11|numeric',
                'email' => 'required|email'
        ];
        try {
            $this->validate($request, $validate);
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }
        
        $sellerId = $this->getUserId();
        $seller = User::where('seller_id', $sellerId)->first();
        if(!$seller) {
            return $this->dataResponse('seller_details_not_found', null, 'error');
        }

        //get all edited fields
        $updateFields = [];
        $validationArray = [];
        foreach ($validate as $key=>$value) {

            if ($request[$key] !== $seller[$key]){
                $updateFields[$key] = $request[$key];
                $validationArray[$key] = $validate[$key];
                if ($key == 'email') 
                    $validationArray['email'] ='required|email|unique:users';
                
            }
            
        }

        try {
            $this->validate($request, $validationArray);
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $res = User::where('seller_id', $sellerId)->update($updateFields);
        if($res > 0)
            return $this->dataResponse('update_successful', User::where('seller_id', $sellerId)->first());
        
        return $this->dataResponse('update_failed', null, 'error');
    }

 /**
     * @SWG\Post(
     *   path="/seller/verify",
    *   tags={"Seller Module"},
     *   summary="Verify Seller BVN",
     *        @SWG\Parameter(
     *         name="seller",
     *         in="body",
     *         description="BVN [SellerObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="bvn",type="string", description="Seller Bank Verification Number"),     
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200, 
     *      description="{status: success, message: verified, data: [sellerObject]}"),
     *  @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: Some Missing Parameters are required / seller_details_not_found/update_failed }"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     
     * )
     * Update a Seller's Details
     *
     * @return \Illuminate\Http\Response
     */




    //update authenticated seller BVN Verification 
    public function getVerified (Request $request) {
        $validate = [
                'BVN' => 'required|max:11'
        ];
        try {
            $this->validate($request, $validate);
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }
        
        $sellerId = $this->getUserId();
        $seller = User::where('id', $sellerId)->first();
        if(!$seller) {
            return $this->dataResponse('seller_details_not_found', null, 'error');
        }

        ///BVN Verification

        $firstname = $seller->firstname;
        $bvn = $request->input('BVN');

        $data = [
            'firstname' => $firstname,
            'bvn' => $bvn
        ];

        $verify = new Verified();
        $response = $verify->verify_single($data);
        
        $response_array = json_decode($response, true);
        
        if ('success' === $response_array['status']) {
            $seller->verified = 1;
            $seller->bvn = Hash::make($request->input("BVN"));

            $seller->save();

            return $this->dataResponse("verified", null);
        }
        else {

            if ('Failed, Network Error' == $response_array['message'] || 'SERVICE_UNAVAILABLE' == $response_array['data']['status']) {
                return new JsonResponse($response_array, Response::HTTP_CREATED);
            }

            return $this->dataResponse('bvn details unmatched', $response_array, 'error');
        }

    // End of Bvn Validation
    }

/**
 * @SWG\Get(
 *   path="/seller/orders",
 *   tags={"Seller Module"},
 *   summary="Get Orders for Authenticated Seller",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: orders, data: [TransactionObject + CustomerObject]}"),
 *    @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: failed"),
 *  @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */
    ///get all the order for authenticated seller
    public function orders () {
        $sellerId = $this->getUser()->seller_id;
        $orders = Transaction::with(['customers'])->where('beneficiary_merchant_id', $sellerId)->get();
        if($orders)
            return $this->dataResponse('seller_order', $orders);
        
        return $this->dataResponse('failed', null, 'error');
    }

    

     /**
     * @SWG\Post(
     *   path="/seller/order",
     *   tags={"Seller Module"},
     *   summary="Get Details of a single Order.",
     *        @SWG\Parameter(
     *         name="order id",
     *         in="body",
     *         description="Order Unique Identifier",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="id",type="integer", format="int11", description="Order Unique Identifier"),
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200, 
     *      description="{status: success, message: order_details, data: [TransactionObject + [CustomerObject]]}"),
     *  @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: Some Missing Parameter Required /Failed }"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     
     * )
     * Get Details of Specific Order
     *
     * @return \Illuminate\Http\Response
     */
    //get details about specific order
    public function order(Request $request) {
        $sellerId = $this->getUser()->seller_id;
        try {
            $this->validate($request, [
                'id' => 'required|max:11',
            ]);
            $orderId = $request->input('id');
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }
        $order = Transaction::with(['customers'])->where('id', $orderId)->where('beneficiary_merchant_id', $sellerId)->get();
        if($order)
            return $this->dataResponse('order_details', $order);

        return $this->dataResponse('failed', null, 'error');
    }


    /**
 * @SWG\Get(
 *   path="/seller/order/disputes",
 *   tags={"Seller Module"},
 *   summary="Get Disputes for Orders for Authenticated Seller",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: disputes, data: [RefundRequestObject + {TransactionObject}]}"),
 *    @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: failed"),
 *  @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the dipsutes.
 *
 * @return \Illuminate\Http\Response
 */
    ///get all the order for authenticated seller

    public function disputes () {
        $sellerId = $this->getUser()->seller_id;

        $disputes = Seller::find($sellerId)->refundRequest()->with('transactions')->get();
        if($disputes) 
            return $this->dataResponse('dipsutes', $disputes);
       
        return $this->dataResponse('failed', null, 'error');
    }
    /** @SWG\Post(
        *   path="/seller/order/dispute",
        *   tags={"Seller Module"},
        *   summary="Get Single Dispute Details",
        *  @SWG\Parameter(
            *         name="dispute_id",
            *         in="body",
            *         description="Dispute Unique Id",
            *         required=true,
            *         @SWG\Schema(
            *             type="object",
            *              @SWG\Property(property="dispute_id",type="integer",format="int64", description="Dispute Unique Id greater than 1"),
            *         )
            *     ),
        *   @SWG\Response(
        *      response=200, 
        *      description="{status: success, message: disputes, data: [RefundRequestObject + {TransactionObject}]}"),
        * @SWG\Response(
        *      response=201, 
        *      description="{status: error, message:Some Missing Parameters Required / Failed}"),
        * @SWG\Response(
        *      response=401, 
        *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
        * )
        *
        * Display a Dispute Detail of the resource.
        *
        * @return \Illuminate\Http\Response
        */
       
           //view a single dispute
           public function dispute (Request $request) {
            $sellerId = $this->getUser()->seller_id;
            try {
                   $this->validate($request, [
                       'dispute_id' => 'required',
                   ]);
                   $disputeId = $request->input('dispute_id');
               } catch (ValidationException $e) {
                   return $this->dataResponse($this->getMissingParams($e), null, 'error');
               }
               $dispute = RefundRequest::where('id', $disputeId)->with('transactions')->first();
               if($dispute)
                    return $this->dataResponse('dispute_details', $dispute);

                return $this->dataResponse('failed', null, 'error');
           }
     /**
 * @SWG\Post(
 *   path="/seller/order/dispute/raise",
 *   tags={"Seller Module"},
 *   summary="Raise a  Dispute ",
 *  @SWG\Parameter(
     *         name="dispute",
     *         in="body",
     *         description="Dispute Details",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="transaction_id",type="integer",format="int64", description="Target Transaction Unique Id greater than 1"),
     *  @SWG\Property(property="status",type="string", description="Status of the Dispute 0 - Canceled 1 - Pending/Processing 2 - Solved(Default = 1)"),
     *  @SWG\Property(property="reason",type="string", description="Reason for Dispute or refund request"),
     *         )
     *     ),
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: new_dispute, data: [RefundRequestObject]}"),
 *    @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: Some Missing Parameters are Required /Failed}"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Raise an Issue by Seller.
 *
 * @return \Illuminate\Http\Response
 */

    //to raise a dispute 
    public function raiseDispute (Request $request) {
        $sellerId = $this->getUser()->seller_id;

        try {
            $this->validate($request, [
                'transaction_id' => 'required',
                'status' => 'required',
                'reason' => 'required'
            ]);
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $dispute = new RefundRequest;
        $dispute = $request->all();

        $response = RefundRequest::create([
            'transaction_id' =>  $dispute['transaction_id'],
            'status' =>  $dispute['status'],
            'reason' =>  $dispute['reason']
        ]);

        if($response)
            return $this->dataResponse('new_dispute', $response);

        return $this->dataResponse('new_dispute', $response);

    }

         /**
 * @SWG\Post(
 *   path="/seller/order/dispute/cancel",
 *   tags={"Seller Module"},
 *   summary="Cancel a  Dispute ",
 *  @SWG\Parameter(
     *         name="dispute",
     *         in="body",
     *         description="Dispute Unique Id",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="dispute_id",type="integer",format="int64", description="Dispute Unique Id greater than 1"),
     *         )
     *     ),
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: cancel_dispute, data: [RefundRequestObject]}"),
 * @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: Some Missing Parameters Required/Failed }"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Cancel an Issue by Seller.
 *
 * @return \Illuminate\Http\Response
 */
    
    public function cancelDispute (Request $request) {
        $sellerId = $this->getUser()->seller_id;
        try {
            $this->validate($request, [
                'dispute_id' => 'required',
            ]);
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $disputeId = $request->input('dispute_id');
        $res = RefundRequest::where('id', $disputeId)
                    ->update(['status' => 0]);

        if($res) {
             return $this->dataResponse('cancel_dispute', RefundRequest::find($disputeId));
        }

        return $this->dataResponse('failed', null, 'error');
       
    }


     /**
 * @SWG\Post(
 *   path="/seller/payout/create",
 *   tags={"Seller Module"},
 *   summary="Add a PayOut Account ",
 *  @SWG\Parameter(
     *         name="PayOut Details",
     *         in="body",
     *         description="Payout Account Details",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="bank_name",type="string", description="Bank Name of Payout Detail"),
     *              @SWG\Property(property="bank_account_name",type="string", description="Payout Bank Account Name"),
     *              @SWG\Property(property="bank_account_number",type="string", description="PayOut Bank Account Number")
     *         )
     *     ),
    *   @SWG\Response(
    *      response=200, 
    *      description="{status: success, message: account_added, data: [AccountObject]}"),
    * @SWG\Response(
    *      response=201, 
    *      description="{status: error, message: Some Missing Parameters Required/Failed }"),
    * @SWG\Response(
    *      response=401, 
    *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
    * )
    *
    * Add A PayoutDetail.
    *
    * @return \Illuminate\Http\Response
    */
    public function payOutCreate (Request $request) {
        
        $sellerId = $this->getUser()->seller_id;
        $paystack_lib_object = Paystack::make();

        try {
            $this->validate($request, Account::getValidationRules());
            $account = new Account;
            
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $account = $request->all();

        try {
            $transferRes = $paystack_lib_object->createTransferRecipient(
                $account['bank_account_name'],
                $account['bank_account_number'],
                $account['bank_code']
            );

        } catch (Exception $e) {
            $this->dataResponse($e->getMessage(), null, 'error');
        }
        
        $accounts = new Account([
            'bank_name' => $transferRes['details']['bank_name'],
            'bank_account_name' => $transferRes['name'],
            'bank_account_number' => $transferRes['details']['account_number'],
            'bank_code' => $transferRes['details']['bank_code'],
            'transfer_recipient_id' => $transferRes['recipient_code']
        ]);
        
        $seller = Seller::find($sellerId);
        $response = $seller->account()->save($accounts);
        
        return $this->dataResponse('account_added', $response);
    }

/**
 * @SWG\Get(
 *   path="/seller/payout/view",
 *   tags={"Seller Module"},
 *   summary="View PayOut Details of Authenticated Seller",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: account_details, data: [AccountObject]}"),
 *  @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: failed}"),
 *  @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the dipsutes.
 *
 * @return \Illuminate\Http\Response
 */
    public function payOutView () {
        
        $sellerId = $this->getUser()->seller_id;
        $account = Account::with(['users'])->where('user_id', $seller)->first();

        if($account) 
            return Utils::dataResponse('account', $account);
        else
            return Utils::dataResponse('failed', null, 'error');

    }


    /**
 * @SWG\Post(
 *   path="/seller/term/create",
 *   tags={"Seller Module"},
 *   summary="Create a Term",
 *  @SWG\Parameter(
     *         name="term",
     *         in="body",
     *         description="Term Details",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="body",type="string",format="varchar255", description="Body of the Term"),
     *  @SWG\Property(property="SLA",type="string",format="varchar255", description="SLA"),
     *  @SWG\Property(property="logistics_fee",type="string",format="varchar255", description="Logistics Fee")
     *         )
     *     ),
    *   @SWG\Response(
    *      response=200, 
    *      description="{status: success, message: new_term, data: [DeliveryTermObject]}"),
    *  @SWG\Response(
    *      response=201, 
    *      description="{status: error, message: Some Missing Parameters REquired /Failed}"),
    * @SWG\Response(
    *      response=401, 
    *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
    * )
    *
    * Create a Term by Seller
    *
    * @return \Illuminate\Http\Response
    */

    public function createTerm (Request $request) {
        $sellerId = $this->getUser()->seller_id;

        try {
            $this->validate($request, DeliveryTerm::getValidationRule());
        } catch(ValidatonException $e) {
            $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $term = new DeliveryTerm;
        $term = $request->all();

        $response = DeliveryTerm::create([
            'user_id' => $sellerId,
            'body' => $term['body'],
            'SLA' => $term['SLA'],
            'logistics_fee' => $term['logistics_fee']
        ]);
        if($response)
            return $this->dataResponse('term_added', $response);

        return $this->dataResponse('failed', null, 'error');

    }


      /**
 * @SWG\Put(
 *   path="/seller/term/edit/{termId}",
 *   tags={"Seller Module"},
 *   summary="Update/Edit a Term",
 *  @SWG\Parameter(
     *         name="term",
     *         in="body",
     *         description="Term Details",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="body",type="string",format="varchar255", description="Body of the Term"),
     *  @SWG\Property(property="SLA",type="string",format="varchar255", description="SLA"),
     *  @SWG\Property(property="logistics_fee",type="string",format="varchar255", description="Logistics Fee")
     *         )
     *     ),
    *   @SWG\Response(
    *      response=200, 
    *      description="{status: success, message: update_successfully, data: [DeliveryTermObject]}"),
    *     @SWG\Response(
    *      response=201, 
    *      description="{status: error, message: Some Missing Parameters Required /Update Failed, data: [DeliveryTermObject]}"),
    * @SWG\Response(
    *      response=401, 
    *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
    * )
    *
    * Update a Term by Seller
    *
    * @return \Illuminate\Http\Response
    */
    public function editTerm (Request $request, $termId){
        $sellerId = $this->getUser()->seller_id;
        if(!is_numeric($termId))
             return $this->dataResponse('termId is a number', null, 'error');

        try {
            $this->validate($request, DeliveryTerm::getValidationRule());
        } catch(ValidatonException $e) {
            $this->dataResponse($this->getMissingParams($e), null, 'error');
        }
        $term = DeliveryTerm::find($termId);
        
        $updateFields = [];
        foreach ($term->getFillable() as $value=>$key) {
            if($request[$key] !== $term[$key]){
                $updateFields[$key] = $request[$key];
            }
        }
        $updateFields['user_id'] = $sellerId;
        $response = DeliveryTerm::where('id', $termId)->update($updateFields);
        if($response)
            return $this->dataResponse('updated_successfully', DeliveryTerm::find($termId));

        return $this->dataResponse('update_failed', null, 'error');

    }

     /**
 * @SWG\Get(
 *   path="/seller/terms",
 *   tags={"Seller Module"},
 *   summary="Get Terms by Authenticated Seller",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: seller_terms, data: [DeliveryTermObjects + {SellerObject}]}"),
 *  @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: failed}"),
 *  @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the dipsutes.
 *
 * @return \Illuminate\Http\Response
 */

    public function getTerms (){
        $sellerId = $this->getUser()->seller_id;
        
        $terms = DeliveryTerm::where('user_id', $sellerId)->get();
        if($terms)
            return $this->dataResponse('seller_terms', $terms);

        return $this->dataResponse('failed', null, 'error');
    }
 /**
 * @SWG\Post(
 *   path="/seller/order/assign",
 *   tags={"Seller Module", "Order Module"},
 *   summary="Assign a Delivery Man to an Order. Get DeliveryMen using /seller/deliverymen",
 *  @SWG\Parameter(
     *         name="order",
     *         in="body",
     *         description="Order Details",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="order_id",type="string",format="varchar6", description="Transaction Order Number/ID"),
    *               @SWG\Property(property="delivery_man_id",type="integer", description="One of Seller Registered DeliveryMen Unique Id")
     *         )
     *     ),
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: deliveryman_assigned, data: [TransactionObject]}"),
 *  @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: Some Missing Parameters Required /Failed/Invalid Credentials}"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Assign a Delivery Man to Order
 *
 * @return \Illuminate\Http\Response
 */


    public function setDeliveryMan (Request $request) {
        $sellerId = $this->getUser()->seller_id;
        try {
            $this->validate($request, [
                'order_id' => 'required|numeric',
                'delivery_man_id' => 'required|numeric'
            ]);
            
            $order_id = $request->input('order_id');
            $delivery_man_id = $request->input('delivery_man_id');

            $transaction = Transaction::where('id', $order_id)->where('beneficiary_merchant_id', $sellerId)->first();
            $delivery_man = DeliveryMan::where('added_by', $sellerId)->where('id', $delivery_man_id)->first();
            
            if(!$transaction || !$delivery_man)
                return $this->dataResponse('invalid_order_number/invalid_deliveryman', null, 'error');

        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $transaction->delivery_man = $delivery_man_id;
        $response = $transaction->save();

        if($response)
            return $this->dataResponse('deliveryman_assigned', $transaction);

        return $this->dataResponse('failed', null, 'error');
        
    }
     /**
 * @SWG\Get(
 *   path="/seller/deliverymen",
 *   tags={"Seller Module"},
 *   summary="Get Delivery Men of Authenticated Seller",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: delivery_men, data: [DeliveryManObjects]}"),
 *  @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: No Record Found/Failed}"),
 *  @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the dipsutes.
 *
 * @return \Illuminate\Http\Response
 */

    public function getDeliveryMen () {
        $sellerId = $this->getUser()->seller_id;
        $deliverymen = DeliveryMan::where('added_by', $sellerId)->get();
        if($deliverymen->count() > 0)
            return $this->dataResponse('delivery_men', $deliverymen);

        return $this->dataResponse('No Record Found', null, 'error');
    }

    private function getUserId () {
        return JWTAuth::parseToken()->authenticate()->id;
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
