<?php

namespace App\Providers;

use App\Models\Notification;
use App\Classes\NotificationForUser;
use Illuminate\Support\ServiceProvider;
use App\Models\NotificationSpecialists;
use App\Classes\NotificationForSpecialist;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        NotificationSpecialists::created(function ($specialistsNotifications) {
            NotificationForSpecialist::checkRecipients($specialistsNotifications);
        });

        Notification::created(function ($notification) {
            if($notification->notifications_message_id == 38 || $notification->notifications_message_id == 39){
                NotificationForUser::checkRecipientsReply($notification);
            }else{
                NotificationForUser::checkRecipients($notification);
            }
        });
        Notification::updated(function ($notification) {
            if($notification->notifications_message_id != 27){
                NotificationForUser::checkRecipients($notification);
            }
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }

}
