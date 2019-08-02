<?php

namespace App\Classes;

use App\Models\User;
use App\Models\Group;
use App\Models\FcmSpecialist;
use App\Models\PushSubscription;
use App\Models\NotificationMessage;
use App\Models\NotificationSpecialists;

class NotificationForSpecialist
{
    /**
     * Check Recipient for push notification
     *
     * @param $notification
     */
    public static function checkRecipients($notification)
    {
        $fcmUser = FcmSpecialist::whereSpecialistId($notification->notifiable)->first();
        if (count($fcmUser)){
            PushNotificationToSpecialistApp::sendPushNotification($notification, $fcmUser->fcm_id);
        }

        $expert = PushSubscription::whereUserId($notification->notifiable)->first();
        if (count($expert)){
            WebPushNotificationToSpecialist::sendPushNotification($notification, $expert->fcm_token);
        }

        SendEmailToSpecialist::checkEmailNotificationType($notification);
    }

    /**
     * Create notification sor specialists
     *
     * @param $question_id
     * @param $notifiable
     * @param $notifier_id
     * @param $notification_message
     */
    public static function createNotificationForSpecialist($question_id, $notifiable, $notifier_id, $notification_message)
    {
        $notifications_message_id = self::getNotificationMessageId($notification_message);
        $getNotifiable = User::find($notifiable);
        switch ($getNotifiable) {
            case $getNotifiable->email == 'psychosocial@maya.com.bd':
                self::createGroupNotification(1, $question_id, $notifier_id, $notifications_message_id);
                break;
            case $getNotifiable->email == 'legal@maya.com.bd':
                self::createGroupNotification(2, $question_id, $notifier_id, $notifications_message_id);
                break;
            case $getNotifiable->email == 'public-health@maya.com.bd':
                self::createGroupNotification(3, $question_id, $notifier_id, $notifications_message_id);
                break;
            case $getNotifiable->email == 'medical@maya.com.bd':
                self::createGroupNotification(4, $question_id, $notifier_id, $notifications_message_id);
                break;
            case $getNotifiable->email == 'beauty@maya.com.bd':
                self::createGroupNotification(5, $question_id, $notifier_id, $notifications_message_id);
                break;
            case $getNotifiable->email == 'pediatric@maya.com.bd':
                self::createGroupNotification(6, $question_id, $notifier_id, $notifications_message_id);
                break;
            case $getNotifiable->email == 'gynecology@maya.com.bd':
                self::createGroupNotification(7, $question_id, $notifier_id, $notifications_message_id);
                break;
            case $getNotifiable->email == 'tech@maya.com.bd':
                self::createGroupNotification(8, $question_id, $notifier_id, $notifications_message_id);
                break;
            case $getNotifiable->email == 'dental@maya.com.bd':
                self::createGroupNotification(9, $question_id, $notifier_id, $notifications_message_id);
                break;
            default:
                self::createNotification($question_id, $notifiable, $notifier_id, $notifications_message_id);
        }
    }

    /**
     * Get notification message id
     *
     * @param $notification_message_type
     * @return null
     */
    public static function getNotificationMessageId($notification_message_type)
    {
        $notificationType = NotificationMessage::whereType($notification_message_type)->first();

        if (count($notificationType)){
            return $notificationType->id;
        }

        return null;
    }

    /**
     * Create Notification
     *
     * @param $question_id
     * @param $notifiable
     * @param $notifier_id
     * @param $notifications_message_id
     */
    public static function createNotification($question_id, $notifiable, $notifier_id, $notifications_message_id)
    {
        NotificationSpecialists::create([
            'question_id' => $question_id,
            'notifiable' => $notifiable,
            'notifier_id' => $notifier_id,
            'notification_message_id' => $notifications_message_id
        ]);
//        if ($notifications_message_id == 1){
//            Miscellaneous::realtimeUpdate($question_id, 'refer');
//        }
    }

    /**
     * Insert into database to create group notification
     *
     * @param $group_id
     * @param $question_id
     * @param $notifier_id
     * @param $notifications_message_id
     */
    public static function createGroupNotification($group_id, $question_id, $notifier_id, $notifications_message_id)
    {
        $groups = Group::find($group_id);
        $groups->users->reject(function ($user) use ($notifier_id) {
            return $user->id == $notifier_id;
        })->map(function ($user) use ($notifier_id, $notifications_message_id, $question_id) {
            return NotificationSpecialists::create([
                'question_id' => $question_id,
                'notifiable' => $user->id,
                'notifier_id' => $notifier_id,
                'notification_message_id' => $notifications_message_id
            ]);
        });
    }

}
