<?php

namespace App\Listeners;

use App\Classes\Miscellaneous;
use App\Models\User;
use App\Events\AnswerWasPost;
use App\Classes\NotificationForUser;
use App\Models\NotificationSpecialists;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Classes\NotificationForSpecialist;
use Illuminate\Support\Facades\Log;

class AnswerPost implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AnswerWasPost $event)
    {
        try{
            if ($event->cause == 'AnswerUpdateNotification') {
                $this->createAnswerUpdateNotification($event->question, $event->answer);
            } else {
                $this->createAnswerAndReferrerNotification($event->question, $event->answer);
                Miscellaneous::createAutomaticFollowUp($event->question, $event->answer);
            }
        } catch (\Exception $exception) {
//            Log::emergency($exception->getMessage(), ['file' => $exception->getFile(), 'line' => $exception->getLine()]);
        }

    }

    public function failed(AnswerWasPost $event, $exception)
    {
        Log::emergency(json_encode($event) . $exception->getMessage() . ' '. $exception->getLine() . ' '. $exception->getTraceAsString());
    }

    public function createAnswerUpdateNotification($question, $answer)
    {
        try{
            $notifiable = User::find($question->user_id);
            if (count($notifiable) && $question->source != 'robi') {
                NotificationForUser::createNotification($question, $notifiable->id, $answer->user_id, 'Updated');
            }
        }catch (\Exception $exception){
//            Log::emergency($exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }

    public function createAnswerAndReferrerNotification($question, $answer)
    {
        try{
            $notifiable = User::find($question->user_id);
            if ($question->source != 'robi' || $question->source != 'airtel' || $question->source != 'messenger') {
                NotificationForUser::createNotification($question, $notifiable->id, $answer->user_id, 'Answered');
            }

            $referrer = NotificationSpecialists::whereQuestionId($question->id)->whereNotificationMessageId(1)->orderBy('created_at', 'desc')->first();
            if (count($referrer) && !empty($answer->user_id)) {
                NotificationForSpecialist::createNotification($question->id, $referrer->notifier_id, $answer->user_id, 'Referrer');
            }
        }catch (\Exception $exception){
//            Log::emergency($exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }

}
