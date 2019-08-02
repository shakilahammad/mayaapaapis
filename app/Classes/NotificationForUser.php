<?php

namespace App\Classes;

use App\Models\GcmUser;
use App\Models\Notification;
use App\Models\NotificationMessage;
use App\Models\Reply;

class NotificationForUser
{
    /**
     * Check Recipients for for notification
     *
     * @param $notification
     */
    public static function checkRecipients($notification)
    {
        $gcmUser = GcmUser::whereUserId($notification->notifiable)->first();

        if (!empty($gcmUser)){
            PushNotificationToUserApp::sendPushNotification($notification, $gcmUser->gcm_id);
        }

        //Send Email Notification to Specialist
//        SendEmailToUser::checkEmailNotificationType($notification);
    }

    public static function checkRecipientsForMayaPoints($notification)
    {
        $gcmUser = GcmUser::whereUserId($notification->notifiable)->first();

        if (!empty($gcmUser)){
            PushNotificationToUserApp::sendMayaPointPushNotification($notification, $gcmUser->gcm_id);
        }

        //Send Email Notification to Specialist
//        SendEmailToUser::checkEmailNotificationType($notification);
    }

    public static function checkRecipientsReply($notification)
    {
        $gcmUser = GcmUser::whereUserId($notification->notifiable)->first();

        $reply = Reply::where('user_id', $notification->notifier_id)->orderBy('created_at', 'desc')->first();

        if (!empty($gcmUser)){
            PushNotificationToUserApp::sendPushNotificationReply($notification, $gcmUser->gcm_id, $reply->body);
        }


        //Send Email Notification to Specialist
//        SendEmailToUser::checkEmailNotificationType($notification);
    }

    /**
     * Create Notification
     *
     * @param $question
     * @param $notifiable
     * @param $notifier_id
     * @param $notifications_message
     */
    public static function createNotification($question, $notifiable, $notifier_id, $notifications_message)
    {
        if($notifiable != $notifier_id) {
            try {
                if ($question->source != 'robi' && $question->source != 'messenger' && $question->source != 'airtel') {
                    $notifications_message_id = self::getNotificationMessageId($notifications_message);

                    $newNotification = Notification::updateOrCreate(
                        ['question_id' => $question->id, 'notifiable' => $notifiable, 'notifications_message_id' => $notifications_message_id],
                        [
                            'question_id' => $question->id,
                            'notifiable' => $notifiable,
                            'notifier_id' => $notifier_id,
                            'notifications_message_id' => $notifications_message_id
                        ]
                    );

                    if (!$newNotification->wasRecentlyUpdated) {
                        $newNotification->increment('count');
                    }
                }
            } catch (\Exception $exception) {
//                \Log::info($exception->getMessage(), ['file' => $exception->getFile(), 'line' => $exception->getLine()]);
            }
        }
    }

    public static function createNotificationReply($question, $notifiable_question_user ,$notifiable_comment_user, $notifier_id)
    {
        if($notifiable_question_user != $notifier_id) {

            try {
                if ($question->source != 'robi' && $question->source != 'messenger' && $question->source != 'airtel') {
                    $notifications_message_id = self::getNotificationMessageId('reply_question');
                    $newNotification = Notification::create(
                        [
                            'question_id' => $question->id,
                            'notifiable' => $notifiable_question_user,
                            'notifier_id' => $notifier_id,
                            'notifications_message_id' => $notifications_message_id
                        ]
                    );

//                    if (!$newNotification->wasRecentlyUpdated) {
//                        $newNotification->increment('count');
//                    }
                }
            } catch (\Exception $exception) {
//                \Log::info($exception->getMessage(), ['file' => $exception->getFile(), 'line' => $exception->getLine()]);
            }
        }


        if($notifiable_comment_user != $notifier_id && $notifiable_comment_user != $notifiable_question_user) {

            try {
                if ($question->source != 'robi' && $question->source != 'messenger' && $question->source != 'airtel') {
                    $notifications_message_id = self::getNotificationMessageId('reply_comment');
                    $newNotification = Notification::create(
                        [
                            'question_id' => $question->id,
                            'notifiable' => $notifiable_comment_user,
                            'notifier_id' => $notifier_id,
                            'notifications_message_id' => $notifications_message_id
                        ]
                    );

//                    if (!$newNotification->wasRecentlyUpdated) {
//                        $newNotification->increment('count');
//                    }
                }
            } catch (\Exception $exception) {
//                \Log::info($exception->getMessage(), ['file' => $exception->getFile(), 'line' => $exception->getLine()]);
            }
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

}
