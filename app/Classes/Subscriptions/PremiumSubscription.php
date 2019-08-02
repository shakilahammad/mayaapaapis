<?php

namespace App\Classes\Subscriptions;

use App\Models\PremiumUser;
use App\Mail\PaymentSuccess;
use App\Models\PremiumPackage;

class PremiumSubscription
{
    public static function success($payment)
    {
        switch ($payment->status) {
            case 'active':
                SubscribeNotification::subscribe($payment);
                self::paymentSuccessMail($payment);
                break;
            case 'expired':
                SubscribeNotification::unSubscribe($payment);
                break;
        }
    }

    private static function paymentSuccessMail($payment)
    {
        $userInfo = PremiumUser::whereInvoiceId($payment->invoice_id)->first();
        $package = PremiumPackage::find($payment->package_id);

        if (filter_var($userInfo->email, FILTER_VALIDATE_EMAIL) == true && !ends_with($userInfo->email, '@phone.com.bd')) {
            \Mail::to($userInfo->email)->queue(new PaymentSuccess($payment, $package, $userInfo));
        }
    }

}
