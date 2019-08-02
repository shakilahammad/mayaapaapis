<?php

namespace App\Events;

use App\Models\Question;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PremiumQuestion implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $question;

    /**
     * Create a new event instance.
     *
     * @param Question $question
     */
    public function __construct(Question $question)
    {
        $this->question = $question;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     */
    public function broadcastOn()
    {
        return new PrivateChannel('new-question', 'answering-now', 'my-question');
    }
}
