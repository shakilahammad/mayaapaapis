<?php
/**
 * Created by PhpStorm.
 * User: razib
 * Date: 2019-07-03
 * Time: 18:44
 */

namespace App\Http\Controllers\Partners\POCO;


use App\Models\PocoQuestion;
use App\Models\PocoSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PocoController
{
    public function CheckSessionStatus(Request $request, $user_id, $session){

        # session nai e
        # session ache but expired
        # session ache

        //TODO
        // Response
            // - is_session_expired - true/false

        $user_last_session = $this->GetUserLastSession($user_id);

        $session = count($user_last_session) ? $user_last_session->session : $session;

        $question_count = $this->QuestionCountWithSession($user_id, $session);

        if(count($user_last_session) || $question_count === 3){
            $data = [
                'status' => 'success',
                'is_session_expired' => true
            ];

            return response()->json($data);
        }
        else{

            $data = [
                'status' => 'success',
                'is_session_expired' => false
            ];

            return response()->json($data);
        }
    }

    public function GetNewSession(Request $request, $user_id){

        //TODO
        // Response
            // - session_id

        $new_session = $this->updateSession();

        $data = [
            'status' => 'success',
            'session' => $new_session->session
        ];

        return response()->json($data);

    }

    public function GetUserLastSession($user_id){

        $session = PocoQuestion::where('user_id', $user_id)->orderBy('session', 'desc')->first();

        return $session;
    }

    public function updateSession(){

        $session = PocoSession::first();

        $session->session = $session->session + 1;
        $session->save();

        return $session;
    }

    public function QuestionCountWithSession($user_id, $session){

        $question_count = PocoQuestion::where('user_id', $user_id)->where('session', $session)->count();

        return $question_count;
    }



}