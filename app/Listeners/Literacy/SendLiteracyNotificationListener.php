<?php

namespace App\Listeners\Literacy;

use App\Models\Question;
use App\Models\QuestionTag;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\Literacy\SendLiteracyNotification;
use App\Jobs\Literacy\SendLiteracyNotificationJob;

class SendLiteracyNotificationListener implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  SendLiteracyNotification $event
     * @return void
     */
    public function handle(SendLiteracyNotification $event)
    {
        try {
            $question = Question::whereStatus('answered')->find($event->questionView->question_id);

            if ($question->exists() && $event->questionView->user_id == $question->user_id) {
                $questionTags = QuestionTag::whereQuestionId($question->id)->get()->pluck('tag_id')->toArray();
                $tags = array_intersect($questionTags, config('admin.prePostTags'));

                if (!empty($tags)) {
                    dispatch(new SendLiteracyNotificationJob($question, $event->questionView, 'Pre-Literacy'));
                }
            }
        }catch (\Exception $exception){
//            \Log::emergency('From Pre-Post: ' .' '. $exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }
}
