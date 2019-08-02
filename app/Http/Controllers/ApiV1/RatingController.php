<?php

namespace App\Http\Controllers\ApiV1;

use App\Models\Answer;
use App\Models\User;
use App\Models\Rating;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\Question_view;
use App\Http\Controllers\Controller;

class RatingController extends Controller
{
    public function storeRate(Request $request)
    {
        $question = Question::with('answer')->find($request->question_id);
        $user = User::find($request->user_id);
        if (!count($question) && !count($user)) {
            return $this->makeResponse('failure', 'Unable to find user!');
        }

        $answer_id = $question->Answer->id;
        if ($request->value == 0 ){
            $ratingValue = 1;
        }else if ($request->value > 5){
            $ratingValue = 5;
        }else{
            $ratingValue = $request->value;
        }

        $isRated = Rating::withUserAndQuestion($user->id, $question->id)->first();
        if (count($isRated)) {
            $isRated->update([
                'rating' => $ratingValue
            ]);

            return $this->makeResponse('success', 'Thanks for update your rating!');
        }

        Rating::create([
            'rating' => $ratingValue,
            'user_id' => $user->id,
            'question_id' => $question->id,
            'answer_id' => $answer_id,
            'source' => 'app'
        ]);

        return $this->makeResponse('success', 'Thanks for your rating!');
    }

    public function checkIsRated($user_id)
    {
        try {
            $question = Question::whereUserId($user_id)->whereStatus('answered')->orderBy('created_at', 'desc')->first();
            $view = Question_view::whereUserId($user_id)->whereQuestionId($question->id)->first();
            $show_rating = Answer::where('answers.question_id', $question->id)
                ->join('question_views as qv', 'answers.question_id', '=', 'qv.question_id')
                ->whereColumn('answers.created_at', '<', 'qv.updated_at')
                ->count();

            if (count($question) && count($view) && $show_rating) {
                if (count($question->Ratings) == 0) {
                    return response()->json([
                        'status' => 'success',
                        'question_id' => $question->id,
                        'body' => html_entity_decode(utf8_decode(strip_tags($question->body))),
                        'error_code' => 0,
                        'error_message' => '',
                    ]);
                }else return $this->makeResponse('failed');
            }else{
                return $this->makeResponse('failed');
            }

            return $this->makeResponse('success');

        } catch (\Exception $exception) {
            return $this->makeResponse('failed');
        }
    }

    private function makeResponse($status, $data = null){
        return response()->json([
            'status' => $status,
            'data' => $data,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }
}
