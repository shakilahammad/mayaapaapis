<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Classes\NotificationForUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UserNotification implements ShouldQueue
{
     use InteractsWithQueue, Queueable, SerializesModels;

     public $question;
     public $notifiable;
     public $notifier_id;
     public $notification_type;

     public function __construct($question, $notifiable, $notifier_id, $notification_type)
     {
         $this->question = $question;
         $this->notifiable = $notifiable;
         $this->notifier_id = $notifier_id;
         $this->notification_type = $notification_type;
     }

     /**
     * Execute the job.
     *
     * @return void
     */
     public function handle()
     {
         NotificationForUser::createNotification($this->question, $this->notifiable, $this->notifier_id, $this->notification_type);
     }

}
