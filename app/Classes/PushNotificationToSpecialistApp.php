<?php

namespace App\Classes;

use App\Models\User;
use App\Models\Question;

class PushNotificationToSpecialistApp
{
    /**
     * Fcm API Key For App
     *
     * @var string
     */
    public static $fcmApiKeyForSpecialistApp = 'AIzaSyDugzohMap3lyYhLf9rtJT7IiWygih-Abs';

    /**
     * FCM URL
     *
     * @var string
     */
    public static $url = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Send Push Notification to User APP
     *
     * @param $notification
     * @param $fcmId
     */
    public static function sendPushNotification($notification, $fcmId)
    {
        $checkloggedIn = User::whereId($notification->notifiable)->exists();
        if ($checkloggedIn){
            $question = Question::find($notification->question_id);
            $user = User::find($notification->notifiable);
            $post = [
                'registration_ids' => [$fcmId],
                'data' => [
                    'title' => $notification->Message->title,
                    'type'  => $notification->Message->type,
                    'body'  => $notification->Message->details,
                    'question_id' => $notification->question_id,
                    'is_premium' => $question->is_premium,
                    'expert_type' => isset($user->specialistProfile) ? $user->specialistProfile->job_type : null
                ]
            ];

            self::sendFCMNotification($post);
        }
    }

    /**
     * Send FCM push Notification to app
     *
     * @param $post
     */
    public static function sendFCMNotification($post)
    {
        $headers = [
            'Authorization: key='.self::$fcmApiKeyForSpecialistApp,
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
