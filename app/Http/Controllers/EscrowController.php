<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\SellerConfirmationMail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;

use App\User as User;
use App\Customer as Customer;
use App\Transaction as Transaction;
use App\RefundRequest as RefundRequest;
use App\Seller as Seller;
use App\DeliveryTerm as DeliveryTerm;
use App\DeliveryMan as DeliveryMan;
use App\Account as Account;

use App\Library\Escrow;

class EscrowController extends Controller
{  
    use Escrow;
    
    public function paySeller (Request $request) {
        try {
            $this->validate($request, [
                'order_code' => 'required'
            ]);
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParam($e), null, 'error');
        }

        $order_code = $request->input('order_code');
        $response = $this->escrowToSeller($order_code);

        $this->dataResponse('seller_paid', $response);
    }

    public function refund (Request $request) {
        try{
            $this->validate($request, [
                'order_code' => 'required',
                'deduct_logistics' => 'boolean'
            ]);
        } catch (ValidationException $e) {
            return $this->dataResponse($this->getMissingParam($e), null, 'error');
        }

        $order_code = $request->input('order_code');
        
        //send a refund to the buyer depending on terms
        if ($request->input('deduct_logistics')) {
            $response = $this->orderCancelation($order_code);
        } else{
            $response = $this->orderCancelation($order_code, false);
        }
        
        $this->dataResponse('refund_paid', $response);

    }

}
