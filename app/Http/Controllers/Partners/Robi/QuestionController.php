<?php

namespace App\Http\Controllers\Partners\Robi;

use App\Classes\SetLocation;
use App\Http\Controllers\Controller;
use App\Http\Helper;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function storeRobiQuestion(Request $request)
    {
        try{
            $user = $this->getUser($request);

            $this->createQuestion($request, $user);

            return response()->json(['status' => 'success']);

        }catch (\Exception $exception){
            return response()->json(['status' => 'failure']);
        }
    }

    public function storeAirtelQuestion(Request $request)
    {
        try{
            $user = $this->getUser($request);

            $this->createQuestion($request, $user);

            return response()->json(['status' => 'success']);

        }catch (\Exception $exception){
            return response()->json(['status' => 'failure']);
        }
    }


    public function getUser($request)
    {
        $user = User::whereEmail(Helper::maya_encrypt($request->phone))->first();
        if (count($user)) return $user;

        $location = SetLocation::formattedLocation($request->ip(), 0, 0, $user->id);
        return User::create([
            'f_name' => 'Anonnymous',
            'email' => $request->phone,
            'phone' => $request->phone,
            'type' => 'user',
            'source' => $request->source,
            'gender' => 'female',
            'registered' => 0,
            'location_id' => $location->id,
        ]);
    }

    public function createQuestion(Request $request, $user)
    {
        Question::create([
            'body' => utf8_encode($request->body),
            'source' => $request->source,
            'user_id' => $user->id,
            'is_premium' => 1,
            'location_id' => $user->location_id
        ]);
    }

}
