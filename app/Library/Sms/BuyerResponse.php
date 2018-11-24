<?php
namespace App\Library\Sms;

use App\Library\Sms\SmsResponse;

class BuyerResponse extends SmsResponse {

    public function requestResponse () {
        //send Sms to Buyer 
        $customer_phone = $this->transaction->customers->phone_number;

        $message = "Dear Buyer, your item with order no. ". $this->order_code ." requires you to accept or reject it. Please reply with Yes to accept or No to reject.";

        $this->sendSms($customer_phone, $message);
    }

    public function acceptResponse () {
        //get seller and customer details
        $seller_phone = $this->transaction->sellers->phone_number;
        $customer_phone = $this->transaction->customers->phone_number;
        
        //seller message
        $seller_message = "Dear Seller, your item with Order No. ". $this->order_code ." has been accepted and you have been credited for same. Thank you. Stanbic TrustPay";
        
        //customer message
        $customer_message = "Dear Buyer, based on your acceptance of the item with Order No. ". $this->order_code .", Seller has been credited. Thank you. Stanbic TrustPay";
        
        //sending messages
        $this->sendSms($seller_phone, $seller_message);
        $this->sendSms($customer_phone, $customer_message);
  
    }

    public function rejectResponse () {
        $deliveryman_phone = $this->transaction->delivery_men->business_phone;
        
        $message = "Dear Seller, your item with order No ". $this->order_code ." has been rejected. Please reply with “Yes” or “No” to this message to confirm possession of the item";

        $this->sendSms($deliveryman_phone, $message);
    }
}