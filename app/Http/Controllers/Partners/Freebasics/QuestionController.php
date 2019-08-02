<?php

namespace App\Http\Controllers\Partners\Freebasics;

use App\Models\Question;
use App\Classes\SetLocation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{
    public function storeQuestion(Request $request)
    {
        try{
            $this->create($request);

            return response()->json(['status' => 'success']);

        }catch (\Exception $exception){
            return response()->json(['status' => 'failure']);
        }
    }

    public function create(Request $request)
    {
        $ip = empty($request->ip) ? $request->ip() : $request->ip;

        $location = SetLocation::formattedLocation($ip, 0, 0, $request->user_id);

        Question::create([
            'body' => utf8_encode($request->body),
            'source' => $request->source,
            'user_id' => $request->user_id,
            'location_id' => $location->id
        ]);
    }

}
