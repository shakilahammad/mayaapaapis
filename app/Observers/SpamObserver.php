<?php

namespace App\Observers;

use App\Models\Spam;
use App\Models\Question;
use App\Classes\Miscellaneous;
use App\Models\PremiumQuestionQueue;

class SpamObserver
{
    public function created(Spam $spam)
    {
        $question = Question::whereId($spam->question_id)->whereIsPremium(1)->get();

        Miscellaneous::realtimeUpdate($question, 'spam');

        $this->deleteFromQueue($spam->question_id);
    }

    private function deleteFromQueue($questionId)
    {
        $queuedQuestion = PremiumQuestionQueue::whereQuestionId($questionId)->first();
        if (isset($queuedQuestion) && count($queuedQuestion)){
            $queuedQuestion->delete();
        }
    }

}
