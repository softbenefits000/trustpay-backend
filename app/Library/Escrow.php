<?php
namespace App\Library;

use App\Transaction as Transaction;
use App\Seller as Seller;
use App\DeliveryTerm as DeliveryTerm;
use App\Library\Utils;
use \MAbiola\Paystack\Paystack as PayStack;

trait Escrow {
    use Utils;
    public function __construct() {
        //get percentage charge on escrows
        $this->escrow_charge = (int) env('ESCROW_PERCENTAGE_FEE');
        $this->interbankCharge = (int) env('INTERBANK_CHARGE');
        $this->gateway_fee = (int) env('PAYMENT_GATEWAY_PERCENTAGE_FEE');
    }

    public function escrowToSeller ($order_code) {
        //get Transaction/Order
        $transaction = Transaction::with(['seller'])->where('order_code', $order_code)->first();
        $term = DeliveryTerm::where('user_id', $transaction->beneficiary_merchant_id)->first();
        $account = Account::where('user_id', $transaction->beneficiary_merchant_id)->first();

        if(!$transaction || !$term || !$account)
            return null;

        $recipient_code = $account->transfer_recipient_id;

        //transfer amount = amount minus fees
        $order_amount = $transaction->amount;
        $percentage_charge = $order_amount * ($this->escrow_charge / 100);
        $transfer_amount = $order_amount - $percentage_charge - $this->interbankCharge;

        return $this->initTransfer($transfer_amount, $recipient_code);
    }

    public function orderCancelation ($order_code, $deductLogistics= true) {
        //get Transaction/Order
        $transaction = Transaction::with(['seller'])->where('order_code', $order_code)->first();
        $term = DeliveryTerm::where('user_id', $transaction->beneficiary_merchant_id)->first();
        $account = Account::where('user_id', $transaction->beneficiary_merchant_id)->first();

        if(!$transaction || !$term || !$account)
            return null;

        //buyer
        $order_amount = $transaction->amount;
        if($deductLogistics)
            $refund  = $order_amount - $logistics_fee - $this->gateway_fee;
        else 
            $refund = $order_amount - $this->gateway_fee;

        $buyerRefund = $this->initRefund($order_code, $refund);

        //seller
        $recipient_code = $account->transfer_recipient_id;
        $logistics_fee = $term->logistics_fee;
        $percentage_charge = $logistics_fee * ($this->escrow / 100);
        
        //transfer amount = amount minus fees
        $transfer_amount = $logistics_fee - $pecentage_charge - $this->interbankCharge;
        $sellerTransfer = $this->initTransfer($transfer_amount, $recipient_code);

        return [
            'buyer_refund' => $buyerRefund,
            'seller_transfer' => $sellerTransfer
        ];
    }


    /**
     * Initiate a Transfer .
     * @param $amount in kobo
     * @param $recipient_code
     * @return array|mixed
     */
    public function initTransfer ($amount, $recipient_code) {
        try {
            return PayStack::make()->createTransfer($amount, $recipient_code);
        } catch(Exception $e) {
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

    /**
     * Initiate a Refund .
     * @param $reference 
     * @param $amount in kobo
     * @return array|mixed
     */
    public function initRefund ($reference, $amount) {
        try {
            return PayStack::make()->createRefund($reference, $amount);
        } catch ( Exception $e) {
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

}