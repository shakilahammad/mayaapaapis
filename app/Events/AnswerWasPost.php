<?php

namespace App\Events;

use App\Models\Question;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class AnswerWasPost
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $question;
    public $answer;
    public $cause;

    /**
     * Create a new event instance.
     *
     * @param Question $question
     * @param $answer
     * @param $cause
     */
    public function __construct(Question $question, $answer, $cause)
    {
        $this->question = $question;
        $this->answer = $answer;
        $this->cause = $cause;
    }

}
