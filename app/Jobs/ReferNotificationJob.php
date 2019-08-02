<?php

namespace App\Jobs;

use App\Models\User;
use App\Http\Helper;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use App\Models\SpecialistProfile;
use App\Classes\NotificationForUser;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Classes\NotificationForSpecialist;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReferNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $question;
    public $referred_to;
    public $referred_by;
    public $notification_type;

    public function __construct($question, $referred_to, $referred_by, $notification_type)
    {
        $this->question = $question;
        $this->referred_to = $referred_to;
        $this->referred_by = $referred_by;
        $this->notification_type = $notification_type;
    }

    /**
     * Execute the job.
     * (referred_to == null && notification_type == FTE+ODE||ODE) when it comes from AutomationWork Listener
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->referred_to === null && $this->notification_type == "FTE+ODE") {
                $this->createPremiumNotification($this->question);
                $this->createNotificationForODE($this->question);
            } elseif ($this->referred_to === null && $this->notification_type == "ODE") {
                $this->createNotificationForODE($this->question);
            } else {
                NotificationForSpecialist::createNotificationForSpecialist(
                    $this->question->id,
                    $this->referred_to,
                    $this->referred_by,
                    $this->notification_type
                );
            }

            $asker = User::find($this->question->user_id);
            if(count($asker)) {
                NotificationForUser::createNotification(
                    $this->question,
                    $asker->id,
                    $this->referred_by,
                    'Refer'
                );
            }

        }catch (\Exception $exception){
//            \Log::info($exception->getMessage() .','. $exception->getLine() .','. $exception->getFile());
        }
    }

    /**
     * Create Premium Question Notification for FTE
     *
     * @param $question
     */
    public function createPremiumNotification($question)
    {
        $specialists = SpecialistProfile::whereJobType('FTE')->get();
        $this->createJobTypeWiseNotification($question, $specialists);

        if($question->source == 'app'){
            Notification::create([
                'question_id' => $question->id,
                'notifiable' => $question->user_id,
                'notifier_id' => $this->referred_by,
                'notifications_message_id' => NotificationForUser::getNotificationMessageId('Plus')
            ]);
        }
    }

    /**
     * Create Notification for ODE
     *
     * @param $question
     */
    public function createNotificationForODE($question)
    {
        $specialists = SpecialistProfile::whereJobType('ODE')->get();

        $this->createJobTypeWiseNotification($question, $specialists);
    }

    /**
     * Create Job Type Wise Notification
     *
     * @param $question
     * @param $specialists
     */
    public function createJobTypeWiseNotification($question, $specialists)
    {
        $system = User::whereEmail(Helper::maya_encrypt('system@maya.com.bd'))->first();
        $specialists->map(function ($specialist) use ($question, $system) {
            NotificationForSpecialist::createNotificationForSpecialist($question->id, $specialist->specialist_id, $system->id, 'New');
        });
    }
}
