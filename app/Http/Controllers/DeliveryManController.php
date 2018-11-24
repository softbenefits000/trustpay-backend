<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Mail\CustomerConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;


use App\Customer as Customer;
use App\Transaction as Transaction;
use App\RefundRequest as RefundRequest;
use App\DeliveryMan as DeliveryMan;
use App\DeliveryMenType as DeliveryMenType;

class DeliveryManController extends Controller
{
     /**
     * @SWG\Post(
     *   path="/deliveryman/create",
     *   tags={"DeliveryMan Module"},
     *   summary="Create New DeliveryMan",
     *        @SWG\Parameter(
     *         name="deliveryMan",
     *         in="body",
     *         description="DeliveryMan Details [DeliveryManObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="type",type="integer", description="DeliveryMan Type"),
     *              @SWG\Property(property="business_name",type="string", description="Delivery Man Name"),
     *              @SWG\Property(property="business_email",type="string", format="date", description="Delivery Man Email"),
     *              @SWG\Property(property="business_address",type="string", description="Delivery Man Address"),
     *              @SWG\Property(property="business_state",type="string", description="Delivery Man State"),
     *              @SWG\Property(property="business_city",type="string", description="Delivery Man City"),
     *              @SWG\Property(property="business_country",type="string", description="Delivery Man Country"),
     *              @SWG\Property(property="business_phone",type="string", description="Delivery Man Phone"),
     *              @SWG\Property(property="siteURL",type="string", description="siteURL"),
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status=success, message=delivery_man_registered, data: [DeliveryManObject]}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status=error, message=failed}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * Add a Delivery Man
     *
     * @return \Illuminate\Http\Response
     */

    public function create (Request $request) {
        $sellerId = $this->getUser()->seller_id;
        
        try {
            $this->validate($request, DeliveryMan::getValidationRules());
        } catch(ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $deliveryMan = new DeliveryMan;
        $deliveryMan = $request->all();

        $response = DeliveryMan::create([
            'type' => $deliveryMan['type'],
            'business_name' => $deliveryMan['business_name'],
            'business_email' => $deliveryMan['business_email'],
            'business_address' => $deliveryMan['business_address'],
            'business_city' => $deliveryMan['business_city'],
            'business_state' => $deliveryMan['business_state'],
            'business_phone' => $deliveryMan['business_phone'],
            'business_country' => $deliveryMan['business_country'],
            'siteURL' => $deliveryMan['siteURL'],
            'added_by' => $sellerId
        ]);

        if($response)
            return $this->dataResponse('deliveryman_created', $response);
        return $this->dataResponse('failed', null, 'error');

    }

/**
 * @SWG\Put(
 *   path="/deliveryman/update/{id}",
 *   tags={"DeliveryMan Module"},
 *   summary="Update Delivery Man",
 *        @SWG\Parameter(
 *         name="deliveryMan",
 *         in="body",
 *         description="eliveryMan Details [DeliveryMan]",
 *         required=true,
 *         @SWG\Schema(
 *             type="object",
 *              @SWG\Property(property="type",type="integer", description="DeliveryMan Type"),
 *              @SWG\Property(property="business_name",type="string", description="Delivery Man Name"),
 *              @SWG\Property(property="business_email",type="string", format="date", description="Delivery Man Email"),
 *              @SWG\Property(property="business_address",type="string", description="Delivery Man Address"),
 *              @SWG\Property(property="business_city",type="string", description="Delivery Man City"),
 *              @SWG\Property(property="business_country",type="string", description="Delivery Man Country"),
 *              @SWG\Property(property="business_phone",type="string", description="Delivery Man Phone"),
 *              @SWG\Property(property="siteURL",type="string", description="siteURL"),
 *         )
 *     ),
 *  
 *   @SWG\Response(
 *      response=200,
 *      description="{status=success, message=updated_successfully, data: [DeliveryManObject]}"),
 *  @SWG\Response(
 *      response=201,
 *      description="{status=error, message=Some Missing Parameters are Required failed}"),
 * @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
 * )
 * 
 * 
 *
 * Add a Delivery Man
 *
 * @return \Illuminate\Http\Response
 */
    public function update (Request $request, $id) {
        $sellerId = $this->getUser()->seller_id;

        $validationRule = DeliveryMan::getValidationRules();
        $validationRule['business_email'] = $validationRule['business_email'].','.$id;
        $validationRule['business_phone'] = $validationRule['business_phone'].','.$id;
        
        try {
            $this->validate($request, $validationRule);
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }
        
        $deliveryMan = DeliveryMan::find($id);
        if(!$deliveryMan)
            return $this->dataResponse('Could not find Record', null, 'error');
        
        $updateFields = [];

        foreach ($deliveryMan->getFillable() as $value=>$key) {
            if($key == 'added_by'){
                continue;
            }
            if($request[$key] !== $deliveryMan[$key]){
                $updateFields[$key] = $request[$key];
            }
        }
        $response = DeliveryMan::where('id', $id)->update($updateFields);
        if($response)
            return $this->dataResponse('updated_successfully', DeliveryMan::find($id));

        return $this->dataResponse('update_failed', null, 'error');
    }
/**
 * @SWG\Get(
 *   path="/deliveryman/view",
 *   tags={"DeliveryMan Module"},
 *   summary="Get Delivery Men Registered to Authenticated Seller",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: delivery_men, data: [DeliveryManObject]}"),
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

    public function view () {

        $sellerId = $this->getUser()->seller_id;
        $deliveryman = DeliveryMan::with(['seller'])->where('added_by', $sellerId)->get();
        if($deliveryman)
            return $this->dataResponse('delivery_men', $deliveryman);
        return $this->dataResponse('Operation Failed', null, 'error');

    }
    
/**
 * /**
     * @SWG\Post(
     *   path="/deliveryman/viewDetails",
     *   tags={"DeliveryMan Module"},
     *   summary="Single Delivery Man Details",
     *        @SWG\Parameter(
     *         name="deliveryMan",
     *         in="body",
     *         description="eliveryMan Details [DeliveryMan]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="delivery_man_id", type="integer", description="DeliveryMan Unique ID"),
     *         )
     *     ),
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: delivery_men, data: [DeliveryManObject]}"),
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

    public function viewDetails (Request $request) {
        $sellerId = $this->getUser()->seller_id;

        try {
            $this->validate($request, [
                'delivery_man_id' => 'required|numeric'
            ]);
            $delivery_man_id = $request->input('delivery_man_id');
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $deliveryman = DeliveryMan::find($delivery_man_id);
        if($deliveryman)
            return $this->dataResponse('delivery_man', $deliveryman);
        return $this->dataResponse('failed', null, 'error');
    }
/**
 * @SWG\Delete(
 *   path="/deliveryman/delete/{id}",
 *   tags={"DeliveryMan Module"},
 *   summary="Delete Delivery Men Registered to Authenticated Seller",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: delivery_men_deleted, data: [DeliveryManObject]}"),
 *    @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: Record Not Found/ failed"),
 *  @SWG\Response(
 *      response=401, 
 *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}")
 * )
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */    



    public function delete($id) {
        $sellerId = $this->getUser()->seller_id;

        $deliveryman = DeliveryMan::find($id);
        if (!$deliveryman)
            return $this->dataResponse('Could not find Record', null, 'error');
        $response = $deliveryman->delete();
        if($response)
            return $this->dataResponse('deliveryman_deleted', $response);
        return $this->dataResponse('delete_failed', null, 'error');
    }
/**
     * @SWG\Post(
     *   path="/deliverymantype/create",
     *   tags={"DeliveryMan Module"},
     *   summary="Create New DeliveryManType",
     *        @SWG\Parameter(
     *         name="deliveryMan",
     *         in="body",
     *         description="DeliveryMan Details [DeliveryManObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="title",type="integer", description="DeliveryMan Type Title"),
     *              @SWG\Property(property="description",type="string", description="Delivery Man Type Description")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status=success, message=delivery_man_type_registered, data: [DeliveryManTypeObject]}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status=error, message=failed}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * Add a Delivery Man Type
     *
     * @return \Illuminate\Http\Response
     */

    public function createDeliveryManType (Request $request) {
        
        try {
            $this->validate($request, DeliveryMenType::getValidationRule());
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $dManType = new DeliveryMenType;
        $dManType = $request->all();

        $response = DeliveryMenType::create([
            'title' => $dManType['title'],
            'description' => $dManType['description']
        ]);

        if($response)
            return $this->dataResponse('delivery_man_type_added', $response);
        return $this->dataResponse('failed', null, 'error');
        
    }
    /**
 * @SWG\Get(
 *   path="/deliverymantype/view",
 *   tags={"DeliveryMan Module"},
 *   summary="Get Delivery Men Types",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: delivery_men_types, data: [DeliveryManTypeObject]}"),
 *    @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: failed")
 * )
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */

    public function viewDeliveryManType () {
        
        $deliveryManTypes = DeliveryMenType::get();
        if($deliveryManTypes)
            return $this->dataResponse('delivery_men_types', $deliveryManTypes);
        return $this->dataResponse('failed', null, 'error');     
    }


    /**
     * @SWG\Put(
     *   path="/deliverymantype/update/{id}",
     *   tags={"DeliveryMan Module"},
     *   summary="Update DeliveryManType",
     *        @SWG\Parameter(
     *         name="deliveryManType",
     *         in="body",
     *         description="DeliveryManType Details [DeliveryManObject]",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="title",type="integer", description="DeliveryMan Type Title"),
     *              @SWG\Property(property="description",type="string", description="Delivery Man Type Description")
     *         )
     *     ),
     *  
     *   @SWG\Response(
     *      response=200,
     *      description="{status: success, message: updated_successfully, data: [DeliveryManTypeObject]}"),
     *  @SWG\Response(
     *      response=201,
     *      description="{status: error, message: Missing Parameters/ failed}"),
     * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
     * )
     * 
     * 
     *
     * Update a Delivery Man Type
     *
     * @return \Illuminate\Http\Response
     */
    public function editDeliveryManType (Request $request, $id) {
        try {
            $this->validate($request, DeliveryMenType::getValidationRule());
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $dManType = DeliveryMenType::find($id);
        if(!$dManType)
            return $this->dataResponse('Could not get record', null, 'error');

        $updateFields = [];
        foreach ($dManType->getFillable() as $value=>$key) {
            if($request[$key] !== $dManType[$key]){
                $updateFields[$key] = $request[$key];
            }
        }

        $response = DeliveryMenType::where('id', $id)->update($updateFields);
        if($response)
            return $this->dataResponse('updated_successfully', DeliveryMenType::find($id));

        return $this->dataResponse('update_failed', null, 'error');
    }

     /**
 * @SWG\Delete(
 *   path="/deliverymantype/delete/{id}",
 *   tags={"DeliveryMan Module"},
 *   summary="Delete Delivery Men Types",
 *   @SWG\Response(
 *      response=200, 
 *      description="{status: success, message: deliverymanType_deleted, data: [DeliveryManTypeObject]}"),
 *    @SWG\Response(
 *      response=201, 
 *      description="{status: error, message: Could Not find Record / failed"),
 * @SWG\Response(
     *      response=401, 
     *      description="{status: error, message: invalid token/token expired, debug: [DebugInfoObject]}"),
 * )
 *
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */
    public function deleteDeliveryManType (Request $request, $id) {
        $deliverymantype = DeliveryMenType::find($id);
        if (!$deliverymantype)
            return $this->dataResponse('Could not find Record', null, 'error');
        $response = $deliverymantype->delete();
        if($response)
            return $this->dataResponse('deliverymen_type_deleted', $response);
        return $this->dataResponse('delete_failed', null, 'error');
    }

    public function orderConfirmation (Request $request) {
        try {
            $this->validate($request, [
                'orderCode' => 'required|min:6|max:6',
                'confirm_response' => 'required|string|min:6|max:6'
            ]);
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParams($e), null, 'error');
        }

        $orderCode = $request->input('orderCode');
        $confirmResponse = $request->input('confirm_respoonse');

        if ($confirmResponse == 'accept')
            $status == "Rejected";
        
        ///Logic to set Transaction Status to Rejected
        //Send Notification to Seller
        //Refund Buyer 
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
