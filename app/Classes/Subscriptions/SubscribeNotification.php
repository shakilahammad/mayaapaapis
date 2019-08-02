<?php

namespace App\Classes\Subscriptions;

use Carbon\Carbon;
use App\Models\GcmUser;
use App\Classes\PushNotificationToUserApp;

class SubscribeNotification
{
    CONST SubscribeType = 'Subscribe';
    CONST UnSubscribeType = 'Unsubscribe';
    CONST CONGRATULATION = 'অভিনন্দন';
    CONST ALERT = 'Reminder!';
    CONST STATUSACTIVITY = 'com.maya.mayaapaapp.Activities.Generic.MainActivity';
    CONST PACKAGEACTIVITY = 'com.maya.mayaapaapp.Activities.Generic.MayaPremiumPackageActivity';

    public static function subscribe($payment)
    {
//        if ($payment->package_id != 6) {

            list($gcmUser, $package) = self::getUserAndPackage($payment);

            $expiryTime = self::getFormattedExpiryTime($payment);

            $details = "আপনি {$package->name_bn} প্ল্যানে সাবস্ক্রাইব করেছেন। আপনার প্ল্যানের মেয়াদ {$expiryTime} তারিখ পর্যন্ত।";

            $data = self::makeData(
                $details,
                self::CONGRATULATION,
                $gcmUser->gcm_id,
                self::STATUSACTIVITY,
                'স্ট্যাটাস দেখুন'
            );

            self::sendNotification($data);

//        }
    }

    public static function unSubscribe($payment)
    {
        list($gcmUser, $package) = self::getUserAndPackage($payment);

        $details = "আপনি {$package->name_bn} প্ল্যানে থেকে আনসাবস্ক্রাইব করেছেন। আপনার পছন্দ অনুযায়ী অন্য প্ল্যান সাবস্ক্রাইব করতে আমাদের প্লানগুলো দেখুন।";

        $data = self::makeData(
            $details,
            self::CONGRATULATION,
            $gcmUser->gcm_id,
            self::PACKAGEACTIVITY,
            'প্যাকেজ দেখুন'
        );

        self::sendNotification($data);
    }

    public static function sendAlert($payment)
    {
        list($gcmUser, $package) = self::getUserAndPackage($payment);

        $expiryTime = self::getFormattedExpiryTime($payment);

        $details = "আপনার সাবস্ক্রাইব করা {$package->name_bn} প্ল্যানের মেয়াদ {$expiryTime} তারিখে শেষ হয়ে যাবে।";

        $data = self::makeData(
            $details,
            self::ALERT,
            $gcmUser->gcm_id,
            self::STATUSACTIVITY,
            'স্ট্যাটাস দেখুন'
        );

        self::sendNotification($data);
    }

    private static function makeData($details, $subject, $gcmId, $className, $btnText)
    {
        return [
            'registration_ids' => [$gcmId],
            'data' => [
                "subject" => $subject,
                "message" => $details,
                "noti_type" => "custom",
                "noti_task" => "activity",
                "class_name" => $className,
                "image_url" => "https://image.ibb.co/j01itz/push_maya_apa_plus.png",
                "header_Text" => $subject,
                "details_text" => "<p>{$details}</p>",
                "btn_text" => $btnText,
                "log_in_needed" => 'no',
                "question_id" => "33",
                "article_id" => ""
            ]
        ];
    }

    private static function getFormattedExpiryTime($payment): string
    {
        return Carbon::parse($payment->expiry_time)->format('d M Y D g:i A');
    }

    private static function getUserAndPackage($payment): array
    {
        return [
            GcmUser::whereUserId($payment->user_id)->first(),
            $payment->premiumPackage
        ];
    }

    private static function sendNotification($data): void
    {
        PushNotificationToUserApp::sendGCMNotification($data);
    }

}
