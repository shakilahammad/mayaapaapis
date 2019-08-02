<?php

namespace App\Classes;

use App\Models\Question;
use Carbon\Carbon;
use App\Models\GcmUser;
use App\Models\NotificationMessage;
use App\Models\NonPremiumNotification;

class NonPremiumNotificationClass
{
    public static function sendNonPremiumNotificationToUser()
    {
        //if question is not answered or updated yet
        //send this notification and <<update to>> next notification ie: which++ until it goes to 4
        $notifications = NonPremiumNotification::all();
        foreach ($notifications as $notification) {
            if (Carbon::parse($notification->send_at) < Carbon::now()) {
                if (Question::find($notification->question_id)->status == 'pending') {
                    //send push notification to user and update
                    self::createNonPremiumNotification($notification->question_id,
                        $notification->notifiable,
                        $notification->notifier_id,
                        $notification->notifications_message_id,
                        self::getWhich($notification->notifications_message_id) + 1
                    );

                    $gcmUser = GcmUser::whereUserId($notification->notifiable)->first();
                    if (count($gcmUser)) {
                        PushNotificationToUserApp::sendPushNotification($notification, $gcmUser->gcm_id);
                    }
                } else {
                    $notification->delete();
                }
            }
        }

    }

    public static function createNonPremiumNotification($question_id, $notifiable, $notifier_id, $notifications_message_id, $which)
    {
        //create notification according to which
        switch ($which) {
            case 1:
                NonPremiumNotification::create([
                    'question_id' => $question_id,
                    'notifiable' => $notifiable,
                    'notifier_id' => $notifier_id,
                    'notifications_message_id' => $notifications_message_id,
                    'send_at' => Carbon::now()->addMinutes(10)
                ]);
                break;
            case 2:
                NonPremiumNotification::where('question_id', $question_id)
                    ->where('notifiable', $notifiable)
                    ->update([
                        'notifications_message_id' => NotificationMessage::whereType('non_prem_2')->first()->id,
                        'send_at' => Carbon::now()->addMinutes(15)
                    ]);
                break;
            case 3:
                NonPremiumNotification::where('question_id', $question_id)
                    ->where('notifiable', $notifiable)
                    ->update([
                        'notifications_message_id' => NotificationMessage::whereType('non_prem_3')->first()->id,
                        'send_at' => Carbon::now()->addMinutes(20)
                    ]);
                break;
            default:
                NonPremiumNotification::where('question_id', $question_id)
                    ->where('notifiable', $notifiable)
                    ->delete();
        }

    }

    public static function getWhich($message_id)
    {
        switch (NotificationMessage::find($message_id)->type) {
            case 'non_prem_1':
                return 1;
                break;
            case 'non_prem_2':
                return 2;
                break;
            case 'non_prem_3':
                return 3;
                break;
            default :
                return 4;
        }
    }

}
