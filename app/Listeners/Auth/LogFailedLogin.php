<?php

namespace App\Listeners\Auth;

use Illuminate\Http\Request;
use App\Models\ExpertActivityLog;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
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
     * @param  Failed $event
     * @return void
     */
    public function handle(Failed $event)
    {
        try {
            $data = [
                'ip' => $this->request->ip(),
                'user_agent' => $this->request->header('User-Agent')
            ];
            ExpertActivityLog::create([
                'expert_id' => $event->user->id,
                'type' => 'login_failed',
                'data' => $data
            ]);
        }catch (\Exception $exception) {}
    }
}
