<?php

namespace App\Listeners\Auth;

use Illuminate\Http\Request;
use App\Models\ExpertActivityLog;
use Illuminate\Auth\Events\Registered;

class LogRegisteredUser
{
    /**
     * Create the event listener.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {
        try {
            $data = [
                'ip' => $this->request->ip(),
                'user_agent' => $this->request->header('User-Agent')
            ];

            ExpertActivityLog::create([
                'expert_id' => $event->user->id,
                'type' => 'create_user',
                'data' => $data
            ]);
        } catch (\Exception $exception) {}
    }
}
