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

use App\Library\Sms\Sms;
use App\Library\Verified\Verified;



class SMSController extends Controller
{
    public function smsCallback (Request $request) {
        $from = $request->input('from');
        $to = $request->input('to');
        $text = $request->input('text');

        $sms = new Sms($from, $to);
        $sms->messageProcess($text);
    }

    public function verifiedTest (Request $request) {
        
        $firstname = $request->input('firstname');
        $bvn = $request->input('bvn');

        $data = [
            'firstname' => $firstname,
            'bvn' => $bvn
        ];
        $verify = new Verified();
        $response = $verify->verify_single($data);

        return new JsonResponse(json_decode($response, true));

        
    }
}
