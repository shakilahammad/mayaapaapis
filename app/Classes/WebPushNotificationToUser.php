<?php

namespace App\Classes;

class WebPushNotificationToUser
{
    /**
     * Fcm API Key for web
     *
     * @var string
     */
//    public static $fcmApiKeyForWeb = 'AIzaSyBVfavL_YndgzNg3GfY9xuCkhBf5bgcJH4';
    public static $fcmApiKeyForWeb = 'AAAAQUo0e_I:APA91bHNP9fHxFgS5_b8Cy07J4uNUhQVBTFeghbJbdRb-pDXtAB-0TXOu_qcxRgne6VQmjnJ8-jwBEhSx2dKDar6M0W3o7euzgRku8Yy_JAa8QdR4NY_gy3xw1-XDoaK-iMssgcoZsvB';

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
        $data = [
            'title' => $notification->Message->title,
            'type'  => $notification->Message->type,
            'body'  => $notification->Message->details,
            'icon'  => "public/img/manifest-logo.png"
        ];

        $post = [
            'registration_ids' => [$fcm_token],
            'notification' => $data,
            'data' => [
                'title' => $notification->Message->title,
                'type'  => $notification->Message->type,
                'body'  => $notification->Message->details,
                'question_id' => $notification->question_id,
            ]
        ];

        self::sendFCMNotification($post);
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
