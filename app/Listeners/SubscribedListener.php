<?php

namespace App\Listeners;

use App\Events\Subscribed;
use App\Models\PremiumPayment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Classes\Subscriptions\PremiumSubscription;

class SubscribedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  Subscribed  $event
     * @return void
     */
    public function handle(Subscribed $event)
    {
        try {
            $payment = PremiumPayment::find($event->payment->id);

            PremiumSubscription::success($payment);

        }catch (\Exception $exception){
//            \Log::emergency($exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }
}
