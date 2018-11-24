<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomerConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $firstname; 
    public $email; 
    public $phone; 
    public $code; 
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($firstname, $email, $phone, $code, $url)
    {
        $this->firstname = $firstname;
        $this->email = $email;
        $this->phone = $phone;
        $this->code = $code;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Register with Online TrustPay')
            ->view('mails.confirmation.customer');
    }
}
