<?php

namespace App\Http\Middleware;

use Barryvdh\Debugbar\LaravelDebugbar;
use Carbon\Carbon;
use Closure;
use App\Models\AccessToken;
use DebugBar\DebugBar;
use Illuminate\Support\Facades\Log;

class CheckClientCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->hasHeader('access-token')){

//            Log::emergency(request()->headers);

            $accessToken = $request->header('access-token');



            $accessToken = AccessToken::whereToken($accessToken)->first();

//            dd($accessToken->user_id );

//            if($accessToken->user_id == 301844){
//                \Barryvdh\Debugbar\Facade::enable();
//            }
//            else{
//                \Barryvdh\Debugbar\Facade::disable();
//            }

            if (count($accessToken)){
                $accessToken->update([
                    'last_requested_at' => Carbon::now()
                ]);

                return $next($request);
            }
        }

        return $this->sendErrorResponse();
    }

    private function sendErrorResponse()
    {
        return response()->json([
            'status' => 'authentication-failed',
            'data' => null,
            'error_code' => 1,
            'error_message' => 'Authentication failed!',
        ]);
    }
}
