<?php

namespace App\Events;

use App\Models\User;
use App\Http\Helper;
use App\Models\Question;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class QuestionWasCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $question;

    public $system;

    /**
     * Create a new event instance.
     *
     * @param $question
     */
    public function __construct(Question $question)
    {
        $system = User::whereEmail(Helper::maya_encrypt('system@maya.com.bd'))->first();
        $this->question = $question;
        $this->system = $system;
    }

}
