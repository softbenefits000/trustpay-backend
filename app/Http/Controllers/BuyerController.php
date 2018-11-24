<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerConfirmationMail;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;



use App\Customer as Customer;
use App\Transaction as Transaction;
use App\RefundRequest as RefundRequest;

class BuyerController extends Controller {
     /**
     * @SWG\Post(
     *   path="/customer/register",
     *   tags={"Buyer Module"},
     *   summary="Create New Customer",
     *        @SWG\Parameter(
     *         name="customer",
     *         in="body",
     *         description="Customer Details [CustomerObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="firstname",type="string", description="Customer Firstname"),
     *              @SWG\Property(property="lastname",type="string", description="Customer Lastname"),
     *              @SWG\Property(property="phone_number",type="string", description="Customer Phone Number"),
     *              @SWG\Property(property="email",type="string", description="Customer Email Address"),
     *              @SWG\Property(property="password",type="string", description="Customer Password"),
     *              @SWG\Property(property="password_confirmation",type="string", description="Confirm Password"),
     *              @SWG\Property(property="PIN",type="integer", description="Customer PIN"),
     *              @SWG\Property(property="PIN_confirmation",type="integer", description="Confirm Customer PIN"),
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
     *      description="{status=error, message=messageText}"),
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
            $this->validate($request, Customer::getRegisterValidation());
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $hasher = app()->make('hash');
        $code = hash_hmac('sha256', str_random(40), config('app.key'));
        $customer = new Customer;
        $customer = $request->all();

        $response = Customer::create([
            'firstname' =>  $customer['firstname'],
            'lastname' =>  $customer['lastname'],
            'phone_number' =>  $customer['phone_number'],
            'email' =>  $customer['email'],
            'password' =>  $hasher->make($customer['password']),
            'PIN' =>  $hasher->make($customer['PIN']),
            'ip_address' =>  $customer['ip_address'],
            'device_type' =>  $customer['device_type'],
            'device_version' =>  $customer['device_version'],
            'confirmation_token' => $code,
        ]);

		Mail::to($customer['email'])->send(new CustomerConfirmationMail($customer['firstname'], $customer['email'], $customer['phone_number'], $code, env("LIVE_URL")));
        return $this->dataResponse('customer_registered', $response);
       
    }

    /**
     * @SWG\Get(
     *   path="/customer/confirmation/{token}",
     *   tags={"Buyer Module"},
     *   summary="Customer Email Confirmation",
     *        @SWG\Parameter(
     *         name="token",
     *         in="query",
     *         type="string",
     *         description="Token from mail sent to user",
     *         required=true,
     *         @SWG\Items(type="string")
     *     ),
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: customer_verified}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: invalid_token}"),
     * )
    /**
     * Handle customer registration token for comfirmation.
     *
     * 
     * @param $token from url
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmBuyer ($token) {

    	$customer= Customer::where('confirmation_token', $token)
    		->where('status', 0)->first();

    	if($customer){

    		if($customer->confirmation_token == $token){
    			$customer->status = 1;

    			if($customer->save()){
    				return $this->dataResponse('customer_verified');
    			}

    		}
    	} 

    	return $this->dataResponse('invalid_token', null, 'error'); 
    }


/**
 * @SWG\Get(
 *   path="/customer",
 *   tags={"Buyer Module"},
 *   summary="Get Authenticated Customer Details",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: customer_details, data: [CustomerObject]}"),
 * @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: failed}"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */
    
    //view customer details
    public function getCustomerDetails (Request $request) {
        
        $id = JWTAuth::parseToken()->authenticate()->id;

        $customer = Customer::where('id', $id)->first();
        if($customer){
            return $this->dataResponse('customer_details', $customer);
        }else {
            return $this->dataResponse('failed', null, 'error');
        }

    }

      /**
     * @SWG\Put(
     *   path="/customer/update",
     *   tags={"Buyer Module"},
     *   summary="Update/Edit Customer Details",
     *        @SWG\Parameter(
     *         name="customer",
     *         in="body",
     *         description="Customer Details [CustomerObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="firstname",type="string", description="Customer Firstname"),
     *              @SWG\Property(property="lastname",type="string", description="Customer Lastname"),
     *              @SWG\Property(property="phone_number",type="string", description="Customer Phone Number"),
     *              @SWG\Property(property="email_address",type="string", description="Customer Email Address"),
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200, 
     *      description="{status: success, message: customer_updated, data: [CustomerObject]}"),
     * @SWG\Response(
     *      response=201, 
     *      description="{status: error, message: some parameters missing or required/failed}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     
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
    //update customer details excluding PIN password and sensitive details
    public function updateCustomer (Request $request) {

        $id = $this->getUserId();

        //validate request parameters
        try {

            $validation = Customer::getValidation();
            $this->validate($request, $validation);

        } catch (ValidationException $e) {
            //get Missing Parameters and fit them into message returning as status 201
            return $this->dataResponse($this->getMissingParams($e), null, 'error');

        }

        $customer = Customer::find($id);
        if(!$customer)
            return $this->dataResponse('Could not get record', null, 'error');


        //get all edited fields
        $updateFields = [];
        $validationArray = Customer::getValidation();
        $validationArray['email'] = 'required|email|unique:customers,email';
        $validationArray['phone_number'] = 'required|numeric|unique:customers';
        $rule = [];
        foreach ($validationArray as $key=>$value) {

            if ($request[$key] !== $customer[$key]){
                $updateFields[$key] = $request[$key];
                $rule[$key] = $customer->getValidation()[$key];
            }
        }

        try {
            $this->validate($request, $rule);
        } catch (ValidationException $e) {
             //get Missing Parameters and fit them into message returning as status 201
             return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }
 
            $res = Customer::where('id', $id)->update($updateFields);
            if(!$res) {
                return $this->dataResponse("Could not Update Customer", null, 'error');
            }

            return $this->dataResponse('customer_updated', Customer::find($id));
    }

     /**
 * @SWG\Get(
 *   path="/customer/orders",
 *   tags={"Order Module"},
 *   summary="Get Orders for Authenticated Customer",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: customer_orders, data: [TransactionObject + SellerObject]}"),
 * @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: failed"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */

    //get a list of orders associated with customer
    public function orders () {

        $id = $this->getUserId();

            $order = Transaction::with(['sellers', 'status', 'user'])->get();
            if($order)
                return $this->dataResponse('customer_orders', $order);

            return $this->dataResponse("failed", null, 'error');
    }

     /**
 * @SWG\Post(
 *   path="/customer/order",
 *   tags={"Order Module"},
 *   summary="Get Single Order Details",
 *  @SWG\Parameter(
     *         name="id",
     *         in="body",
     *         description="Customer id",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="id",type="integer",format="int64", description="Order Unique Id greater than 1"),
     *         )
     *     ),
 *   @SWG\Response(
 *      response=200, 
 *      description="status: success, message:order_details, data: [TransactionObject"),
 * @SWG\Response(
 *      response=201, 
 *      description="status: error, message:Missing parameter Required / Failed"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */

    //get single order details
    public function order (Request $request) {
        
        $customerId = $this->getUserId();

        try {
            $this->validate($request, [
                'id' => 'required|max:11',
            ]);
        } catch (ValidationException $e) {
           return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $orderId = $request->input('id');

        $order = Transaction::with(['sellers', 'status', 'user'])->where('customer_id', $customerId)->where('id', $orderId)->first();
        if($order)
            return $this->dataResponse('order_details', $order);
        
        return $this->dataResponse('Could not Get Orders', null, 'error');

    }


     /**
 * @SWG\Get(
 *   path="/customer/order/disputes",
 *   tags={"Order Module"},
 *   summary="Get All Disputes for Authenticated Customer",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message:customer_disputes, data: [RefundRequestObject]}"),
 * @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: Could not Get Disputes}"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */

    //all disputes by the user logged in
    public function disputes () {

        $customerId = $this->getUserId();

            // $disputes = RefundRequest::with(['transaction'])->where('transaction.customer_id', $customerId)->get();
            $disputes = Customer::find($customerId)->refundRequest()->with('transactions')->get();
            if($disputes)
                return $this->dataResponse('customer_disputes', $disputes);

            return $this->dataResponse('Could not Get Disputes', null, 'error');

    }


     /**
 * @SWG\Post(
 *   path="/customer/order/dispute",
 *   tags={"Order Module"},
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
 *      description="{status: success, message:dispute_details, data: [RefundRequestObject + TransactionObject]}"),
 * @SWG\Response(
 *      response=201, 
 *      description="{status: error, message:Some Missing Parameters are Required / Failed}"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */

    //view a single dispute
    public function dispute (Request $request) {
        $customerId = $this->getUserId();

        try {
            $this->validate($request, [
                'dispute_id' => 'required',
            ]);
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }
        $disputeId = $request->input('dispute_id');
        

        $dispute = RefundRequest::with('transactions')->where('id', $disputeId)->get();
        if($dispute)
            return $this->dataResponse('dispute_details', $dispute);
        
        return $this->dataResponse('failed', null, 'error');
    }

     /**
 * @SWG\Post(
 *   path="/customer/order/dispute/raise",
 *   tags={"Order Module"},
 *   summary="Raise a  Dispute ",
 *  @SWG\Parameter(
     *         name="dispute",
     *         in="body",
     *         description="Dispute Details",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="transaction_id",type="integer",format="int64", description="Target Transaction Unique Id greater than 1"),
     *  @SWG\Property(property="status",type="string", description="Status of the Dispute 0 - Canceled 1 - Pending/Processing 2 - Solved "),
     *  @SWG\Property(property="reason",type="string", description="Reason for Dispute or refund request"),
     *         )
     *     ),
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: new_dispute, data: [DisputeObject]}"),
 *  @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: Missing Parameters are Required / failed}"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */

    //to raise a dispute 
    public function raiseDispute (Request $request) {
        $customerId = $this->getUserId();

        try {
            $this->validate($request, [
                'transaction_id' => 'required',
                'status' => 'required',
                'reason' => 'required'
            ]);
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $transactionId = $request->input('transaction_id');
        
        $dispute = new RefundRequest;
        $dispute = $request->all();

        $res = RefundRequest::create($request->all());
        if($res) {
            return $this->dataResponse('new_dispute', $res);
        }
        else {
            return $this->dataResponse("failed", null, 'error');
        }
    }

      /**
 * @SWG\Post(
 *   path="/customer/order/dispute/cancel",
 *   tags={"Order Module"},
 *   summary="Cancel a Dispute ",
 *  @SWG\Parameter(
     *         name="dispute_id",
     *         in="body",
     *         description="Dispute Details",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="dispute_id",type="integer",format="int64", description="Dispute Unique Id greater than 1"),
     *         )
     *     ),
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: cancel_dispute, data: [DisputeObject]}"),
 * @SWG\Response(
 *      response=201, 
 *      description="{status success, message: failed})",
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * ))
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */

    //to cancel a dispute 
    public function cancelDispute (Request $request) {
        $cutomerId = $this->getUserId();

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
        
        if($res)
            return $this->dataResponse('cancel_dispute', RefundRequest::find($disputeId));
        return $this->dataResponse('failed', null, 'error');
    }

    /**
 * @SWG\Post(
 *   path="/customer/purchase",
 *   tags={"Order Module"},
 *   summary="Create a Purchase Transaction",
 *  @SWG\Parameter(
     *         name="purchase",
     *         in="body",
     *         description="Purchase Details",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              ref="#/definitions/Transaction"
     *         )
     *     ),
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: new_transaction, data: [TransactionObject]}"),
 * @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: Some Parameters Missing are Required / Failed}"),
 *  @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * ))
 *
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */

    // purchase endpoint for logged in user
    public function purchase (Request $request) {
        //get user
        $customerId = $this->getUserId();

        try {
            $this->validate($request, [
                'type' => 'required',
                'customer_id' => 'required',
                'beneficiary_merchant_id' => 'required',
                'payment_reference' => 'required|max:255',
                'amount_payed' => 'required',
                'response_code' => 'required',
                'response_description' => 'required',
                'status' => 'required',
                'product_delivery_status' => 'required',
                'transaction_date' => 'required',
                'delivery_date' => 'required'
            ]);
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $transaction = new Transaction;
        $transaction = $request->all();

        $resp = Transaction::create($request->all());
        if($resp) {
            return $this->dataResponse('new_transaction', $resp);
        }
        
        return $this->dataResponse('failed', null, 'error');

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

    private function getMissingParams (ValidationException $e) {
       
        $message = "";
        foreach($e->getResponse()->getOriginalContent() as $key=>$value) {
            $message = $message . $value[0].'\n';
        }
        return $message;
    }

    //to get authenticated user
    private function getUserId () {
        return JWTAuth::parseToken()->authenticate()->id;
    }
}