<?php

namespace App\Classes;

use App\Models\Log;
use App\Models\Question;
use App\Models\LatestThread;
use App\Models\CommentQuestion;

class DitectThread
{
    public static function threadCalculation($question)
    {
        try{
            $self = new static();
            $lastQuestion = $self->fetchPreviousQuestion($question);

            $latestThread = $self->fetchLatestThread();
            $currentThreadNumber = ++$latestThread->thread;
            $checkCommentQuestion = $self->checkCommentQuestion($question);


            if (!count($lastQuestion)){
                $self->updateQuestion($question, $currentThreadNumber);
                $self->updateLatestThreadNumber($latestThread, $currentThreadNumber);
            }elseif ($checkCommentQuestion) {
                $self->updateQuestion($question, $lastQuestion->thread);
            }elseif (!empty($question->parent_id)){
                $self->updateByParentQuestion($question);
            } elseif ($lastQuestion->resolved == 0){
                $self->updateQuestion($question, $lastQuestion->thread);
            } elseif ($lastQuestion->thread == 0 || $lastQuestion->resolved == 1) {
                $self->updateQuestion($question, $currentThreadNumber);
                $self->updateLatestThreadNumber($latestThread, $currentThreadNumber);
            }
        }catch (\Exception $exception){
//            Log::emergency($question->id .' ' . $exception->getMessage() . ' ' . $exception->getFile() . ' '. $exception->getLine());
        }

    }

    private function checkCommentQuestion($question)
    {
         return CommentQuestion::where('question_id', $question->id)->exists();
    }

    private function fetchPreviousQuestion($question)
    {
        return Question::where('id', '<', $question->id)
            ->where('user_id', $question->user_id)
            ->where('thread', '>', 0)
            ->where('status', '!=', 'spam')
            ->orderBy('id', 'desc')
            ->first();
    }

    private function fetchLatestThread()
    {
        return LatestThread::first();
    }

    private function updateByParentQuestion($question)
    {
        $parentQuestion = Question::find($question->parent_id);
        $this->updateQuestion($question, $parentQuestion->thread);
    }

    private function updateQuestion($question, $currentThreadNumber)
    {
        if ($currentThreadNumber == 0){
            $latestThread = $this->fetchLatestThread();
            $currentThreadNumber = $latestThread->thread ++;
            $this->updateLatestThreadNumber($latestThread, $currentThreadNumber);
        }

        $question->update([
            'thread' => $currentThreadNumber
        ]);
    }

    private function updateLatestThreadNumber($latestThread, $currentThreadNumber)
    {
        $latestThread->update([
            'thread' => $currentThreadNumber
        ]);
    }
}
