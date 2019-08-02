<?php

namespace App\Events;

use App\Models\Refer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ReferSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $refer;

    /**
     * Create a new event instance.
     * @param Refer $refer
     */
    public function __construct(Refer $refer)
    {
        $this->refer = $refer;
    }

}
