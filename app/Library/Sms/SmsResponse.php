<?php
namespace App\Library\Sms;
use AfricasTalking\SDK\AfricasTalking;

use App\Transaction as Transaction;
use App\Customer as Customer;
use App\Seller as Seller;
use App\DeliveryMan as DeliveryMan;
use App\SmsTransaction as SmsTransaction;

abstract class SmsResponse {
    
    protected $order_code;
    protected $transaction;

    public function __construct (Transaction $transaction) {

        $this->transaction = $transaction;
        $this->order_code = $transaction->order_code;

        $username = env("AFRICASTALKING_USERNAME");
        $apiKey = env("AFRICASTALKING_API_KEY");

        $afriTalk = new AfricasTalking($username, $apiKey);
        $afriTalk_sms = $afriTalk->sms();
        $this->afriTalk_sms = $afriTalk_sms;

    }

    public function sendSms ($to, $message) {
        $shortCode = env("AFRICASTALKING_SHORTCODE");

        $options = 
        [
            'from' => $shortCode,
            'to' => $to,
            'enqueue' => false,
            'message' => $message
        ];

        $this->afriTalk_sms->send($options);
    }

    public  abstract function acceptResponse ();
    public abstract function rejectResponse ();

}