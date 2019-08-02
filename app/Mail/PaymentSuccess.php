<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentSuccess extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;

    public $package;

    public $userInfo;

    /**
     * Create a new message instance.
     *
     * @param $payment
     * @param $package
     * @param $userInfo
     */
    public function __construct($payment, $package, $userInfo)
    {
        $this->payment = $payment;
        $this->package = $package;
        $this->userInfo = $userInfo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.payment-success')
                    ->subject('Maya Apa - Payment Success!');
    }
}
