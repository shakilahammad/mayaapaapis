<?php

namespace App\Listeners;

use App\Models\Reply;
use App\Models\CommentQuestion;
use App\Events\AnswerShouldBeReply;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnswerShouldBeReplyListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AnswerShouldBeReply $event)
    {
        try {
            $question = $event->question;
            $commentQuestion = CommentQuestion::where('question_id', $question->id)->first();
            $answer = $event->answer;
            if ($commentQuestion) {
                Reply::create([
                    'user_id' => $answer->user_id,
                    'body' => strip_tags(utf8_decode($answer->body)),
                    'who' => 'Maya Apa',
                    'comment_id' => $commentQuestion->comment_id,
                    'source' => $answer->source
                ]);
            }
        }catch (\Exception $exception){
//            \Log::info($exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }
}
