<?php

namespace App\Events\Literacy;

use App\Models\Question_view;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SendLiteracyNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $questionView;

    /**
     * Create a new event instance
     *
     * @param $questionView
     */
    public function __construct(Question_view $questionView)
    {
        $this->questionView = $questionView;
    }

}
