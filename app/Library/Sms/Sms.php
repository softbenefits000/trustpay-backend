<?php
namespace App\Library\Sms;

use AfricasTalking\SDK\AfricasTalking;

use App\Transaction as Transaction;
use App\Customer as Customer;
use App\Seller as Seller;
use App\DeliveryMan as DeliveryMan;
use App\SmsTransaction as SmsTransaction;

use App\Library\BuyerResponse;
use App\Library\DeliveryManResponse;

use App\Library\Escrow;

class Sms {
    use Escrow;

    private $from;
    private $to;

    private $order_code;

    public function __construct($from , $to) {
        //create a new sms object with env username and apiKey
        //save as private object
        $this->from = $from;
        $this->to = $to;

    }

    public function messageProcess ($smsText) {
        //get sent message from a post request and determine what kind of message
        //initialize
        //deliverycofirm
        //buyer Response
        if(empty($smsText)){
            $this->invalidResponseMessage();
            die();
        }

        $components = explode(" ", $smsText);
        $command = strtoupper($components[0]);

        $order_code = $components[1];
            $this->setOrderCode($order_code);

        if (isset($components[2])){
            $uResponse = strtoupper($components[2]);
        }
        
        switch($command){
            case 'DELIVERY':

                //initializing transaction
                $this->initialize();
                break;

            case 'ORDER':

                //buyer response
                $this->buyer($uResponse);
                break;

            case 'CONFIRM':

                //deliveryman response
                $this->confirm($uResponse);
                break;

            default:
                $this->invalidResponseMessage();
        }
        //split message text by space 
        //i = 0 - command
        //i = 1 - orderCode 
        //i = 2 - response
    }

    public function setOrderCode ($order_code) {
        $this->order_code = $order_code;
    }

    public function getOrderCode () {
        return $this->order_code;
    }

    //recieved
    public function initialize () {
        
        //check if deliveryman number matches order_code
        //add transaction
        $transaction = Transaction::with(['delivery_men', 'customers', 'sellers'])->where('order_code', $this->order_code)->first();
        if(!$transaction){
            $this->invalidResponseMessage();
        }

        // check if phone number matches assigned deliveryman
        if($transaction->delivery_men->business_phone == $this->from){

            //create new sms_transaction
            $exist = SmsTransaction::where('order_code', $this->order_code)->first();       
            if($exist){
                $this->invalidResponseMessage();
            }

            SmsTransaction::create([
                'order_code' => $this->order_code,
                'status' => 'Awaiting Buyer Response'
            ]);
            
            //send message to buyer asking for accept or reject
            $sms_response = new BuyerResponse ($transaction);
            $sms_response->requestResponse();

        }else{
            $this->invalidResponseMessage();
        }

    }

    ///received
    public function buyer ($uResponse) {
        //check if the order_code is valid, transaction is valid and access to transaction is valid
        $transaction = Transaction::with(['delivery_men', 'customers', 'sellers'])->where('order_code', $this->order_code)->first();
        $sms_transaction = SmsTransaction::where('order_code', $this->order_code)->first();

        if(!$transaction || $transaction->customers->phone_number !== $this->from || !$sms_transaction ||  $sms_transaction->buyer_response !== null) {
            $this->invalidResponseMessage();
            
        }
        //TODO set status, transaction status to Accepted/Rejected

        switch($uResponse){
            case 'YES':
                //buyer accept
                /*
                *Dependent on Escrow. Module
                *TODO function to credit seller and update Transaction status
                * 
                */
                //TODO ReArrange The Status Using Constants

                //Credit Seller
                $this->escrowToSeller($this->order_code);

                //Update Status {id 5 = Complete}
                $transaction->status = 5;
                $transaction->save();

                //update sms transaction
                $sms_transaction->update([
                    'status' => 'Accepted',
                    'buyer_response' => $uResponse
                ]);
                
                $buyer_response = new BuyerResponse($transaction);
                $buyer_response->acceptResponse();

                break;
            case 'NO':
                 /*
                *Dependent on Escrow. Module
                *TODO function update Transaction status
                * 
                */
                
                //Update Status {id 5 = Rejected}
                $transaction->status = 3;
                $transaction->save();

                //buyer reject
                $sms_transaction->update([
                    'status' => 'Rejected',
                    'buyer_response' => $uResponse
                ]);

                $buyer_response = new BuyerResponse($transaction);
                $buyer_response->rejectResponse();
                break;
            default:
                $this->invalidResponseMessage(); 
        }
    }
    //received
    public function confirm ($uResponse) {
        $transaction = Transaction::with(['delivery_men', 'customers', 'sellers'])->where('order_code', $this->order_code)->first();
        $sms_transaction = SmsTransaction::where('order_code', $this->order_code)->first();

        if(!$transaction || $transaction->delivery_men->business_phone !== $this->from || !$sms_transaction ||  $sms_transaction->delivery_man_response !== null || !($sms_transaction->buyer_response == "NO") ) {
            $this->invalidResponseMessage();
            
        }
        //TODO set status, transaction status to Accepted/Rejected

        switch($uResponse){
            case 'YES':
                //buyer accept

                /*
                *Dependent on Escrow. Module
                * 
                * 
                */

                //Refund Buyer
                $this->orderCancelation($this->order_code, true);

                //Update Status {id 3 = Rejected}
                $transaction->status = 3;
                $transaction->save();

                //update sms transaction
                $sms_transaction->update([
                    'status' => 'Delivery Man Confirmed',
                    'delivery_man_response' => $uResponse
                ]);
                
                $deliveryman_response = new DeliveryManResponse($transaction);
                $deliveryman_response->acceptResponse();

                break;
            case 'NO':
                 /*
                *Dependent on Escrow. Module
                *TODO function update Transaction status
                * 
                */
                
                //Update Status {id 4 = Rejected}
                $transaction->status = 4;
                $transaction->save();

                //buyer reject
                $sms_transaction->update([
                    'status' => 'Delivery Man Rejected',
                    'delivery_man_response' => $uResponse
                ]);

                $deliveryman_response = new DeliveryManResponse($transaction);
                $deliveryman_response->rejectResponse();
                break;
            default:
                $this->invalidResponseMessage(); 
        }
    }

    // private function update_sms_transaction ($order_code, $options) {
    //     $sms_transaction = SmsTransaction::where('order_code', )
    //     return $sms_transaction->update($options);
    // }

    public function invalidResponseMessage () {
        //give user error response on message
        
        /**
         * Option 1: Return a Message of Invalid Transaction to the Sending Party
         *  Note this options carries the cost of increasing the charges on africa'stalking subscription API
         * 
         * Option 2: Return a No Message and exit the current running script
         *  This option is carried out in the code below.
         */
        echo "Some Wrong SomeWhere";
        exit();
    }

}