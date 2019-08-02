<?php

namespace App\Listeners\Auth;

use Illuminate\Http\Request;
use App\Models\ExpertActivityLog;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
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
     * @param  Login $event
     * @return void
     */
    public function handle(Login $event)
    {
        try{
            if ($event->user->type != 'user') {
                ExpertActivityLog::create([
                    'expert_id' => $event->user->id,
                    'type' => 'login',
                    'data' => [
                        'ip' => $this->request->ip(),
                        'user_agent' => $this->request->header('User-Agent')
                    ]
                ]);
            }
        } catch (\Exception $exception) {}
    }
}
