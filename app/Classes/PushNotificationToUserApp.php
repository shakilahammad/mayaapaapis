<?php

namespace App\Classes;

use Illuminate\Support\Facades\Log;

class PushNotificationToUserApp
{
    /**
     * Gcm API Key for user app
     *
     * @var string
     */
    public static $fcmApiKey = 'AIzaSyBVfavL_YndgzNg3GfY9xuCkhBf5bgcJH4';

    /**
     * GCM URL
     *
     * @var string
     */
    public static $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Send Push Notification to User APP
     *
     * @param $notification
     * @param $gcmId
     */
    public static function sendPushNotification($notification, $gcmId)
    {
        $data = [
            'detail' => $notification->Message->details,
            'subject' => $notification->Message->title,
            'message' => $notification->Message->details,
            'type' => $notification->Message->type,
            'question_id' => $notification->question_id
        ];

        $post = [
            'registration_ids' => [$gcmId],
            'data' => $data
        ];

        self::sendGCMNotification($post);
    }

    public static function sendMayaPointPushNotification($notification, $gcmId)
    {
        Log::emergency('Razib sendMayaPointPushNotification');
        $data = [
//            'detail' => $notification->Message->source_sub_title_en,
//            'subject' => $notification->Message->source_title_en,
//            'message' => $notification->Message->source_sub_title_en,
            'message_en' => isset($notification->Message->message_en) ? $notification->Message->message_en : "",
            'message_bn' => isset($notification->Message->message_bn) ? $notification->Message->message_bn : "",
            "source_title_en"=> isset($notification->Message->source_title_en) ? $notification->Message->source_title_en : "",
            "source_title_bn"=> isset($notification->Message->source_title_bn) ? $notification->Message->source_title_bn : "",
            "source_sub_title_en"=> isset($notification->Message->source_sub_title_en) ? : "",
            "source_sub_title_bn"=>isset($notification->Message->source_sub_title_bn) ? $notification->Message->source_sub_title_bn : "",
            "source_type"=>isset($notification->Message->source_type) ? $notification->Message->source_type : "",
            "action_type" => isset($notification->Message->action_type) ? $notification->Message->action_type : "",
            "earned_point_for_the_action"=>$notification->Message->earned_point_for_the_action,
            "total_point"=>$notification->Message->total_point,
            "current_batch"=>$notification->Message->current_batch,
            "is_badge_just_upgraded"=> $notification->Message->is_badge_just_upgraded,
            "next_upper_badge"=> isset($notification->Message->next_upper_badge) ? $notification->Message->next_upper_badge : 0,
            "noti_type" => 'point'
        ];

        $post = [
            'registration_ids' => [$gcmId],
            'data' => $data
        ];

        $headers = [
            'Authorization: key=' . self::$fcmApiKey,
            'Content-Type: application/json'
        ];

        self::sendGCMNotificationPoint($post);
    }

    public static function sendPushNotificationReply($notification, $gcmId, $message = '')
    {
//        dd($gcmId);
        $data = [
            'detail' => $notification->Message->details .'\n' . $message,
            'subject' => $notification->notifier_id == 45468 ? 'Expert Replied' : $notification->Message->title,
            'message' => $message,
            'type' => $notification->Message->type,
            'question_id' => $notification->question_id
        ];

        $post = [
            'registration_ids' => [$gcmId],
            'data' => $data
        ];

        self::sendGCMNotification($post);
    }

    /**
     * Send FCM push Notification to app
     *
     * @param $post
     */
    public static function sendGCMNotification($post)
    {
        $headers = [
            'Authorization: key=' . self::$fcmApiKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $return = curl_exec($ch);

//        dd($return);

        Log::emergency('loc' . json_encode($post));

        curl_close($ch);
    }

    public static function sendGCMNotificationPoint($post)
    {
        Log::emergency('Razib sendGCMNotification');

        $headers = [
            'Authorization: key=' . self::$fcmApiKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $return = curl_exec($ch);

//        dd($return);

        Log::emergency('loc' . json_encode($post));

        curl_close($ch);
    }

    public static function sendBULKGCMNotification($post)
    {
        $headers = [
            'Authorization: key=' . self::$fcmApiKey,
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
