<?php

namespace App\Observers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\AnswerHistory;
use App\Classes\Miscellaneous;
use App\Events\AnswerShouldBeReply;
use App\Jobs\FollowNotificationJob;
use App\Models\PremiumQuestionQueue;

class AnswerObserver
{
    /**
     * Listen to the User created/updated event.
     *
     * @param   Answer  $answer
     * @return void
     */
    public function created(Answer $answer)
    {
        $question = $this->fetchQuestion($answer);
        $this->createAnswerHistory($answer, 'answered');

//        if ($question->is_premium == 1) {
//            Miscellaneous::sendAnswerToIODUser($question, $answer);
//        } elseif ($question->source == 'messenger'){
//            Miscellaneous::sendAnswerToMessenger($answer, $question);
//        }

        event(new AnswerShouldBeReply($question, $answer));

        dispatch(new FollowNotificationJob($answer));

        $this->updatePremiumQueue($question);
    }

    public function updated(Answer $answer)
    {
        $question = $this->fetchQuestion($answer);
        $this->createAnswerHistory($answer, 'updated');

        $this->updatePremiumQueue($question);
    }

    private function createAnswerHistory($answer, $status)
    {
        $previousHistry = AnswerHistory::whereAnsweredBy($answer->user_id)->whereAnswerId($answer->id)->first();

        if (isset($previousHistry) && count($previousHistry)){
            $previousHistry->update([
                'answer_body' => $answer->body,
            ]);
        }else {
            AnswerHistory::create([
                'question_id' => $answer->question_id,
                'answer_id' => $answer->id,
                'answered_by' => $answer->user_id,
                'answer_body' => $answer->body,
                'score' => 0,
                'source' => isset($answer->source) ? $answer->source : 'app',
                'status' => $status,
            ]);
        }
    }

    private function updatePremiumQueue($question)
    {
        if ($question->status === 'answered') {
            $queuedQuestion = PremiumQuestionQueue::whereQuestionId($question->id)->first();
            if (isset($queuedQuestion) && count($queuedQuestion)) {
                $queuedQuestion->update([
                    'status' => 'answered'
                ]);
            }
        }
    }

    private function fetchQuestion(Answer $answer)
    {
        return Question::find($answer->question_id);
    }

}
