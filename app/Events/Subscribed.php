<?php

namespace App\Events;

use App\Models\PremiumPayment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class Subscribed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $payment;

    /**
     * Create a new event instance.
     *
     * @param $payment
     */
    public function __construct(PremiumPayment $payment)
    {
        $this->payment = $payment;
    }

}
