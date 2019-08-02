<?php

namespace App\Events;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class AnswerShouldBeReply
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $question;
    public $answer;

    public function __construct(Question $question, Answer $answer)
    {
        $this->question = $question;
        $this->answer = $answer;
    }

}
