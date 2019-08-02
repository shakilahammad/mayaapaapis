<?php

namespace App\Classes;

use App\Models\Question;

class WebPushNotificationToSpecialist
{
    /**
     * Fcm API Key for web
     *
     * @var string
     */
    public static $fcmApiKeyForWeb = 'AIzaSyDlnf3-CLWSWLzzwbMqIce1yFq1YMflBKw';

    /**
     * FCM URL
     *
     * @var string
     */
    public static $url = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Send Web Push Notification
     *
     * @param $notification
     * @param $fcm_token
     */
    public static function sendPushNotification($notification, $fcm_token)
    {
        list($sound, $icon) = static::checkPremium($notification->question_id);

        $data = [
            'title' => $notification->Message->title,
            'type'  => $notification->Message->type,
            'body'  => $notification->Message->details,
            'icon'  => $icon,
            'sound' => $sound
        ];
        $post = [
            'registration_ids' => [$fcm_token],
            'notification' => $data,
            'data' => [
                'title' => $notification->Message->title,
                'type'  => $notification->Message->type,
                'body'  => $notification->Message->details,
                'question_id' => $notification->question_id,
                'specialist_id' => $notification->notifiable,
                'icon' => $icon,
                'sound' => $sound
            ]
        ];

        self::sendFCMNotification($post);
    }


    /**
     * Check Premium to set icon
     *
     * @param $question_id
     * @return array
     */
    public static function checkPremium($question_id)
    {
        $question = Question::find($question_id);
        $sound = "/img/audio/notification.mp3";
        if (config('config.APP_ENV') == 'production'){
            $siteUrl = 'https://maya-apa.com';
        }else{
            $siteUrl = url('/');
        }

        $premiumOrNormal = $question->is_premium == 1 ?  "/public/images/maya-plus.png" : "/public/images/logo-main.png";
        $icon = $siteUrl.''.$premiumOrNormal;
        $soundUrl = $siteUrl.''.$sound;
        return [$soundUrl, $icon];
    }

    /**
     * Send FCM push Notification to app
     *
     * @param $post
     */
    public static function sendFCMNotification($post)
    {
        $headers = [
            'Authorization: key='.self::$fcmApiKeyForWeb,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_exec($ch);
        curl_close($ch);
    }

}
