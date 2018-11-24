<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Mail\CustomerConfirmationMail;
use App\Mail\OrderCreationCustomerMail;
use App\Mail\OrderSellerConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Support\Str;




use App\Customer as Customer;
use App\Transaction as Transaction;
use App\RefundRequest as RefundRequest;
use App\DeliveryMan as DeliveryMan;
use App\DeliveryManType as DeliveryManType;
use App\TransactionStatus as TransactionStatus;

use \MAbiola\Paystack\Paystack as PayStack;

class OrderController extends Controller
{   
     /**
     * @SWG\Post(
     *   path="/order/create",
     *   tags={"Order Module"},
     *   summary="Create New Transaction/Order",
     *        @SWG\Parameter(
     *         name="order",
     *         in="body",
     *         description="Order Details [CustomerObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="type",type="integer", description="Transaction Type"),
     *              @SWG\Property(property="delivery_date",type="date", description="Delivery Date"),
     *              @SWG\Property(property="amount_payed", type="integer", format="date", description="Amount to Be Payed in Nigerian Kobo (Please Note)"),
     *              @SWG\Property(property="customer_id", type="integer", description="Unique Id of Buyer"),
     *              @SWG\Property(property="beneficiary_merchant_id", type="integer", description="Unique Id of Seller"),
     *              @SWG\Property(property="delivery_location",type="string", description="Delivery Location")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status:success, message=order_created, data:[OrderObject]}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status:error, message: Missing Parameters Required/failed_order_creation/failed}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * Create a Transaction
     *
     * @return \Illuminate\Http\Response
     */
    //create
    public function create (Request $request) {
        $customerId = $this->getUserId();

        try {
            $this->validate($request, Transaction::getValidationRule());
        } catch (ValidationException $e) {
            $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $transaction = new Transaction;
        $transaction = $request->all();

        //generate OrderCode for the Transaction
        $ordercode = $this->generateOrderCode();

        $response = Transaction::create([
            'type' => $transaction['type'],
            'customer_id' => $transaction['customer_id'],
            'beneficiary_merchant_id' => $transaction['beneficiary_merchant_id'],
            'amount_payed' => $transaction['amount_payed'],
            'order_code' => $ordercode,
            'delivery_date' => $transaction['delivery_date'],
            'delivery_location' => $transaction['delivery_location'],
            'status' => 6
            //default status is "Created" with id 6 on transaction_statuses table
        ]);

        if($response){
            return $this->dataResponse('order_created', $response);
        } else{
            return $this->dataResponse('failed_order_creation', null, 'error');
        }
        
    }

    /**
     * @SWG\Post(
     *   path="/order/pay",
     *   tags={"Order Module"},
     *   summary="Get Payment Authorization for a Transaction/Order",
     *        @SWG\Parameter(
     *         name="order",
     *         in="body",
     *         description="Order Details ",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="customer_id", type="integer", description="Unique Id of Buyer"),
     *              @SWG\Property(property="order_code", type="string", description="Transaction Order Code")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status:success, message:payment_authorization data:[PayStack Authorization Response]}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status:error, message: Missing Parameters Required/failed}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * Get Payment Authorization for a Transaction
     *
     * @return \Illuminate\Http\Response
     */
    //pay
    public function orderPayment (Request $request) {
        try {
            $this->validate($request, [
                'order_code' => 'required|string|size:6',
                'customer_id' => 'required|numeric|min:1'
            ]);

            $customerId = $request->input('customer_id');
            $orderCode = $request->input('order_code');

        } catch (ValidationException $e) {
            $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $transaction = Transaction::where('customer_id', $customerId)->where('order_code', $orderCode)->first();
        $customer = Customer::find($customerId);
        if (!$transaction || !$customer)
            return $this->dataResponse('invalid_details', null, 'error');

        $payStackObject = PayStack::make();
        try {
            $authorization = $payStackObject->startOneTimeTransaction($transaction['order_code'], $transaction['amount_payed'], $customer['email']);
            $response = array('auth' => $authorization);
            return $this->dataResponse('payment_authorization', $response);
        } catch (Exception $e) {
            return $this->dataResponse($e->getMessage(), null, 'error');
        }

        return $this->dataResponse('Something Went Wrong, Try Again', null, 'error');
    }




    private function setResponseDetails ($order_code, $response_code, $response_description, $status = null){
        $transaction = Transaction::where('order_code', $order_code)->first();
        if($status !== null){
            $transaction->status = $status;
        }

        $transaction->response_code = $response_code;
        $transaction->response_description = $response_description;
        $transaction->save();

    }
     /**
     * @SWG\Get(
     *   path="/order/payment/callback",
     *   tags={"Order Module"},
     *   summary="PayStack Callback URL. Do not Implement Apart from Paystack Platform.
     *      Please call /order/view or /payment/verify to View Transaction Details",
     *   @SWG\Response(
     *      response=200,
     *      description="{status:-, message:- data:-}"),
     * )
     * PayStack CallBack URL
     *
     * @return \Illuminate\Http\Response
     */
    
    //payment/callback
    public function orderPaymentCallback ($reference) {
        $payStackObject = PayStack::make();
        try {
            $verification = $payStackObject->verifyTransaction($reference);
            $transaction = Transaction::where('order_code', $reference)->first();
            if(!$transaction)
                return $this->dataResponse('Could not Match OrderCode', null, 'error');
            if($verification) {

                $customer = Customer::find($transaction['customer_id']);
                $seller = Seller::find($transaction['beneficiary_merchant_id']);
                $response = array('verification' => $verification);

                //Update OrderRecord
                //1 == id of Transaction status Pending
                $this->setResponseDetails($reference, 200, $verification, 1);

                //Buyer Mail
                Mail::to($customer['email'])->send(new OrderCreationConfirmation($customer['firstname'], $customer['email'], $transaction['amount_payed'], $transaction['order_code']));


                //Send Seller Mail
                Mail::to($seller['business_email'])->send(new OrderSellerConfirmation($seller['business_name'], $customer['business_email'], $transaction['amount_payed'], $transaction['order_code']));


                return $this->dataResponse('worked', $response);
            }

            $this->setResponseDetails($reference, 201, 'An Error Occurred Making Payment Unsuccessful. Please Try Again.');
            return $this->dataResponse('An Error Occurred Making Payment Unsuccessful. Please Try Again.', null, 'error');
            
        } catch (Exception $e) {

            //add message to payment to db
            $this->setResponseDetails($reference, 400, $e->getMessage());
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
        return $this->dataResponse('failed', null, 'error');
    }

     /**
     * @SWG\Post(
     *   path="/order/payment/verify",
     *   tags={"Order Module"},
     *   summary="Verify Transaction/Order",
     *        @SWG\Parameter(
     *         name="order",
     *         in="body",
     *         description="Order Details ",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="order_code", type="string", description="Transaction Order Code")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status:success, message:verification data:[PayStack Verification Response]}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status:error, message: Missing Parameters Required/failed/invalid_details}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * Verify a Transaction
     *
     * @return \Illuminate\Http\Response
     */

     //payment/verify
    public function orderVerifyPayment (Request $request) {
        try {
            $this->validate($request, [
                'order_code' => 'required|string|size:6'
            ]);
            $order_code  = $request->input('order_code');
            $transaction = Transaction::where('order_code', $order_code)->first();
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        } 

        if(!$transaction)
            return $this->dataResponse('invalid_details', null, 'error');

        $payStackObject = PayStack::make();
        try {
            $verification = $payStackObject->verifyTransaction($order_code);
            $response = array('verification' => $verification);
            return $this->dataResponse('verification', $response);
        } catch (Exception $e) {
            return $this->dataResponse($e->getMessage(), null, 'error');
        }

        return $this->dataResponse('failed', null, 'error');
    }

    /**
     * @SWG\Post(
     *   path="/order/view",
     *   tags={"Order Module"},
     *   summary="View Transaction/Order.",
     *        @SWG\Parameter(
     *         name="order",
     *         in="body",
     *         description="Order Details ",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="order_code", type="string", description="Transaction Order Code")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status:success, message:order_details data:[TransactionObject]}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status:error, message: Missing Parameters Required/failed/invalid_order_code}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * View a Transaction
     *
     * @return \Illuminate\Http\Response
     */
    //order/view
    public function getTransaction (Request $request) {
        $customerId = $this->getUserId();

        try {
            $this->validate($request, [
                'order_code' => 'required|string|size:6'
            ]);

            $order_code = $request->input('order_code');
            $transaction = Transaction::with(['transaction_status'])->where('order_code', $order_code)->first();

            if(!$transaction)
                return $this->dataResponse('invalid_order_code', null, 'error');
        
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }
        
        return $this->dataResponse('order_details', $transaction);
        
    }
    /**
     * @SWG\Post(
     *   path="/order/setStatus",
     *   tags={"Order Module"},
     *   summary="Set Transaction/Order Status. Use /order/allStatus to get all Transaction Statuses (ID)",
     *        @SWG\Parameter(
     *         name="order",
     *         in="body",
     *         description="Order Details ",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="order_code", type="string", description="Transaction Order Code"),
     *              @SWG\Property(property="status", type="integer", description="Transaction Status ID. Use /order/allStatus to get all Transaction Statuses")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status:success, message:status_changed data:-}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status:error, message: Missing Parameters Required/failed/invalid_order_code/invalid_status}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * Set Status for a Transaction
     *
     * @return \Illuminate\Http\Response
     */

    //order/setStatus
    public function setTransactionStatus (Request $request) {
        $customerId = $this->getUserId();

        try {
            $this->validate($request, [
                'order_code' => 'required|string|size:6',
                'status' => 'required|numeric|min:1'
            ]);

            $order_code = $request->input('order_code');
            $status_id = $request->input('status');
            $transaction = Transaction::where('order_code', $order_code)->first();
            $status = TransactionStatus::find($status_id);

            if(!$transaction || !$status)
                return $this->dataResponse('invalid_status or invalid_order_code', null, 'error');

        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $transaction->status = $status_id;
        $response = $transaction->save();

        if($response) {
            return $this->dataResponse('status_changed', $response);
        }else {
            return $this->dataResponse('failed', null, 'error');
        }
    }

     /**
     * @SWG\Post(
     *   path="/order/setDelivery",
     *   tags={"Order Module"},
     *   summary="Set Transaction/Order Delivery Status. ",
     *        @SWG\Parameter(
     *         name="order",
     *         in="body",
     *         description="Order Details ",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="order_code", type="string", description="Transaction Order Code"),
     *              @SWG\Property(property="delivery_status", type="integer", description="Transaction Status ID. 0 - Not Delivered or 1 - Delivered")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status:success, message:delivery_status_changed data:-}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status:error, message: Missing Parameters Required/failed/invalid_order_code/invalid_status}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * Set Delivery Status for a Transaction
     *
     * @return \Illuminate\Http\Response
     */


    //order/setDeliveryStatus
    //deliveryStatus Can be 0 - not delivered and 1 - delivered
    public function setDeliveryStatus (Request $request) {
        $customerId = $this->getUserId();

        try {
            $this->validate($request, [
                'order_code' => 'required|string|size:6',
                'delivery_status' => 'required|numeric|size:1'
            ]);

            $order_code = $request->input('order_code');
            $status_id = $request->input('delivery_status');
            $status = $status_id == 0 || $status_id == 1 ? true : false;
            $transaction = Transaction::where('order_code', $order_code)->first();

            if(!$transaction || !$status)
                return $this->dataResponse('invalid_status or invalid_order_code', null, 'error');

        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $transaction->product_delivery_status = $status_id;
        $response = $transaction->save();

        if($response) {
            return $this->dataResponse('delivery_status_changed', $response);
        }else {
            return $this->dataResponse('failed', null, 'error');
        }
    }

     /**
     * @SWG\Get(
     *   path="/order/allStatus",
     *   tags={"Order Module"},
     *   summary="Get All Transaction/Order Statuses. Use /order/allStatus to get all Transaction Statuses (ID)",
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status:success, message:all_status data: [TransactionStatusObject]}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status:error, message: failed}"),
     * )
     * 
     * 
     *
     * View a TransactionStatuses
     *
     * @return \Illuminate\Http\Response
     */

    //order/allStatus
    public function allStatus () {
        
        $statuses = TransactionStatus::get();

        if($statuses) {
            return $this->dataResponse('all_status', $statuses);
        }else {
            return $this->dataResponse('failed', null, 'error');
        }
    }
     /**
     * @SWG\Put(
     *   path="/order/update",
     *   tags={"Order Module"},
     *   summary="Update Transaction",
     *        @SWG\Parameter(
     *         name="order",
     *         in="body",
     *         description="Order Details ",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="type", type="integer", description="Transaction  Type"),
     *              @SWG\Property(property="order_code", type="string", description="Transaction Order Code."),
     *              @SWG\Property(property="delivery_date", type="date", description="Transaction Delivery Date"),
     *              @SWG\Property(property="delivery_location", type="string", description="Transaction Delivery Location.")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status:success, message:updated data:[TransactionObject]}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status:error, message: Missing Parameters Required/failed/invalid_order_code}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * Update a Transaction
     *
     * @return \Illuminate\Http\Response
     */

    public function update (Request $request) {
        $customerId = $this->getUserId();

        try {
            $this->validate($request, [
                'order_code' => 'required|string|size:6',
                'type' => 'required|numeric|min:1',
                'delivery_date' => 'required|date',
                'delivery_location' => 'required|string|min:4'
            ]);

            $order_code = $request->input('order_code');
            $transaction = Transaction::where('order_code', $order_code)->first();
            
            if(!$transaction)
                return $this->dataResponse('invalid_order_code', null, 'error');
            
            $transaction->type = $request->input('type');
            $transaction->delivery_date = $request->input('delivery_date');
            $transaction->delivery_location = $request->input('delivery_location');
            $response = $transaction->save();

            if($transaction)
                return $this->dataResponse('updated', Transaction::where('order_code', $order_code)->first());
            return $this->dataResponse('failed', null, 'error');

        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }
    }

    /**
     * @SWG\Post(
     *   path="/order/assign",
     *   tags={"Order Module"},
     *   summary="Refresh Order Code for a Transaction/Order. This Should only be used if Paystack gives a duplicate reference message for the current Order Code for a Transaction. Use with Caution.",
     *        @SWG\Parameter(
     *         name="order",
     *         in="body",
     *         description="Order Details ",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="customer_id", type="integer", description="Unique Id of Buyer"),
     *              @SWG\Property(property="order_code", type="string", description="Current Transaction Order Code")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status:success, message:new_order_code_assigned data:[TransactionObject]}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status:error, message: Missing Parameters Required/failed}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * Get Payment Authorization for a Transaction
     *
     * @return \Illuminate\Http\Response
     */
    //order/assign
    public function assign (Request $request) {
         try {
            $this->validate($request, [
                'order_code' => 'required|string|size:6',
                'customer_id' => 'required|numeric|min:1'
            ]);

            $customerId = $request->input('customer_id');
            $orderCode = $request->input('order_code');

        } catch (ValidationException $e) {
            $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $transaction = Transaction::where('customer_id', $customerId)->where('order_code', $orderCode)->first();
        $new_orderCode = $this->generateOrderCode();

        $transaction->order_code = $new_orderCode;
        $response = $transaction->save();

        if($response)
            return $this->dataResponse('new_order_code_assigned', Transaction::where('order_code', $new_orderCode)->first());
        return $this->dataResponse('failed', null, error);
    }


    //TODO Add a function deliveryman confirm to deliveryman API

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

    private function generateOrderCode () {
        $ordercode = "";
        $current = str_random(6);
        $isExist = Transaction::where('order_code', $current)->get();
        if(!($isExist->count() > 0)) {
            $ordercode = $current;
            return $ordercode;
        } else {
            return $this->generateOrderCode();
        }
            
    }

    //mailTesting endpoint Uncomment for mail test
    // public function viewMail () {
    //     //Buyer Mail
    //     Mail::to('adeyemogab@gmail.com')->send(new OrderCreationCustomerMail('Gab Tester', 'adeyemogab@gmail.com', 'gotchatest', '2000'));
    // }
}
