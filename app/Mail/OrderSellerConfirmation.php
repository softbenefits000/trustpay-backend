<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderSellerConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $firstname; 
    public $email; 
    public $order_code; 
    public $amount; 
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($firstname, $email, $amount, $order_code)
    {
        //
        $this->firstname = $firstname;
        $this->email = $email;
        $this->order_code = $order_code;
        $this->amount = $amount;
        
        
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Stanbic TrustPay Escrow Deposit for Order No.'.$this->order_code)
        ->view('mails.order.seller');
    }
}
