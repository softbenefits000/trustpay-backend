<?php

namespace App\Library\Sms;

use App\Library\Sms\SmsResponse;

class DeliveryManResponse extends SmsResponse {
    public function acceptResponse () {
        $customer_phone = $this->transaction->customers->phone_number;

        $message = "Dear Buyer, you will be refunded for Order No. ". $this->order_code ." within 24hours. Thank you. Stanbic TrustPay";

        $this->sendSms($customer_phone, $message);
    }

    public function rejectResponse () {

        $seller_phone = $this->transaction->sellers->business_phone;
        $customer_phone = $this->transaction->customers->phone_number;

        $seller_message = "Dear Seller, following your reply of non possession of item with order no. ". $this->order_code .", kindly log into your account on stanbictrustpay.com to raise a dispute on our dispute portal to facilitate resolution. Failure to raise dispute will automatically effect release of funds to the Buyer after 24 hours. Thank you. Stanbic TrustPay";
        
        $customer_message = "Dear Buyer, the Seller confirms you have not returned the item with Order No. ". $this->order_code .", This will delay your refund. Please agree resolution with the Seller on our dispute portal to facilitate your refund. Thank you. Stanbic TrustPay";

        $this->sendSms($customer_phone, $customer_message);
        $this->sendSms($seller_phone, $seller_message);

    }
}