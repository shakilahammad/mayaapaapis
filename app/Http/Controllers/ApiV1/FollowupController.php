<?php

namespace App\Http\Controllers\ApiV1;

use App\Classes\Miscellaneous;
use App\Classes\NotificationForSpecialist;
use App\Models\FollowUp;
use App\Models\FollowUpQuestion;
use App\Models\Refer;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Question;
use App\Classes\SetLocation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;;

class FollowupController extends Controller
{
    public function index($question)
    {
        try {
            $question = Question::with('answer', 'comments', 'comments.reply', 'followup.followupMessages')->findOrFail($question);
            $this->refine($question);
            $data = [
                "id" => $question->id,
                "body" => $question->body,
                "source" => $question->source,
                "image" => $question->image,
                "area" => $question->area,
                "city" => $question->city,
                "country" => $question->country,
                "type" => $question->type,
                "media_id" => $question->media_id,
                "is_premium" => $question->is_premium,
                "created_at" => $question->created_at_pretty,
                "answer" => [
                    "body" => $question->answer->body,
                    "created_at" => $question->created_at_pretty,
                ],
                "comments" => $question->comments,
                "followup" => $question->followup,
            ];

            return $this->makeResponse('success', $data);

        } catch (\Exception $exception) {
            return $this->makeResponse('failure', null);
        }
    }

    public function getFollowUpHistoryOld($questionId)
    {
        $questions = [];
        try {
            $firstQuestion = Question::with(['answer', 'followup.followupMessages'])->where('id', $questionId)->first();
            array_push($questions, $firstQuestion);
            $parentId = $questionId;
            do {
                $question = Question::with(['answer', 'followup.followupMessages'])->where('parent_id', $parentId)->first();
                if ($question) {
                    array_push($questions, $question);
                    $parentId = $question->id;
                } else {
                    $parentId = 0;
                }
            } while ($parentId != 0);

            $responseData = $this->formattedQuestion($questions);
            $followup_status = $this->getFollowUpStatus($questionId);

            return response()->json([
                'status' => 'success',
                'data' => $responseData,
                'feedback' => $followup_status,
                'error_code' => 0,
                'error_message' => ''
            ]);

//            return $this->makeResponse('success', $data);

        }catch (\Exception $exception){
            return $this->makeResponse('failure', null);
        }
    }

    public function getFollowUpHistory($questionId)
    {
        $questions = [];
        try {
            $firstQuestion = Question::with(['answer', 'followup.followupMessages'])->where('id', $questionId)->first();
            array_push($questions, $firstQuestion);
            $parentId = $questionId;
            $all_questions = Question::with(['answer', 'followup.followupMessages'])->where('parent_id', $parentId)->orderBy('created_at')->get();

            foreach ($all_questions as $question){
                array_push($questions, $question);
            }
            //                        do {
//
//
//                dd($question);
//
//                                if ($question) {
//                    array_push($questions, $question);
//                    $parentId = $question->id;
//                } else {
//                    $parentId = 0;
//                }
//            } while ($parentId != 0);
//            array_push($questions, $question);
//            array_merge($firstQuestion, $question);
            $responseData = $this->formattedQuestion($questions);
            $followup_status = $this->getFollowUpStatus($questionId);

            return response()->json([
                'status' => 'success',
                'data' => $responseData,
                'feedback' => $followup_status,
                'error_code' => 0,
                'error_message' => ''
            ]);

//            return $this->makeResponse('success', $data);

        }catch (\Exception $exception){
            return $this->makeResponse('failure', null);
        }
    }

    private function formattedQuestion($questions)
    {
        $data = [];
        foreach($questions as $question) {
            $value = [
                "id" => $question->id,
                "body" => $this->getRefinedQuestionBody($question->body),
                "source" => $question->source,
                "type" => $question->type,
                "media_id" => $question->media_id,
                "is_premium" => $question->is_premium,
                "created_at" => $this->formattedTime($question->created_at),
                "answer" => $this->formattedAnswer($question->answer),
                "followup_messages" => $this->formattedFollowUpMessages($question)
            ];

            array_push($data, $value);
        }

        return $data;
    }


    public function getFollowUpStatus($question_id, $lang = 'bn'){


        try{
            $follow_up = FollowUp::where('question_id', $question_id)->orderby('created_at', 'desc')->first();

            $responseData = [
                'feedback_status' => !is_null($follow_up->feedback),
                'title_en' => "are you satisfied with our answer ?",
                'title_bn' => "আপনি কি আমাদের উত্তরে সন্তুষ্ট ?"
            ];

            return $responseData;

//            return $this->makeResponse('success', $responseData);
        }catch (\Exception $exception){
            return $this->makeResponse('failure', null);
        }

    }

    public function updateFollowUpStatus(Request $request){

        try{

            $data = $request->input();

            FollowUp::where('question_id', $data['question_id'])->orderby('created_at', 'desc')->first()
                ->update([
                'feedback' => $data['feedback']
            ]);


            return response()->json([
                'status' => 'success',
            ]);

        }catch (\Exception $exception){
            return response()->json([
                'status' => 'failure',
                'message' => $exception->getMessage()
            ]);
        }

    }

    private function formattedAnswer($answer)
    {
        if (empty($answer)) return null;

        return [
            'id' => $answer->id,
            'body' => strip_tags($answer->body),
            'answered_by' => $answer->user_id,
            'created_at' => $this->formattedTime($answer->created_at),
        ];
    }

    private function formattedFollowUpMessages($question)
    {
        $data = [];
        if (empty($question->followup->followupMessages)) return [];

        foreach ($question->followup->followupMessages as $message) {
            $value = [
                'id' => $message->id,
                'followup_id' => $message->followup_id,
                'message_body' => $message->message_body,
                'source' => $message->source,
                'created_at' => $this->formattedTime($message->created_at),
            ];
            array_push($data, $value);
        }

        return $data;
    }

    private function formattedTime($time)
    {
        return Carbon::parse($time)->diffForHumans();
    }

    private function getRefinedQuestionBody($body)
    {
        return strip_tags(utf8_decode($body));
    }

    private function refine($question)
    {
        if (!isset($question->answer)) {
            throw new \Exception("Answer doesn't exist.");
        }

        $question->body = strip_tags(utf8_decode($question->body));
        $question->created_at_pretty = $question->created_at->diffForHumans();
        $question->answer->created_at_pretty = $question->answer->created_at->diffForHumans();

        foreach ($question->comments as $comment) {
            $comment->created_at_pretty = $comment->created_at->diffForHumans();
            $comment->comment = strip_tags(utf8_decode($comment->comment));
        }

        foreach ($question->followup->followupMessages as $followup_message) {
            $followup_message->created_at_pretty = $followup_message->created_at->diffForHumans();
            $followup_message->message_body = strip_tags(utf8_decode($followup_message->message_body));
        }
    }

    public function storeFollowUpQuestionOld(Request $request)
    {
        try {
            $location = SetLocation::formattedLocation($request->ip(), 0, 0, $request->user_id);

//            $followup_expert = FollowUp::where('question_id', $request->parent_id)->first();

//            $question = Question::create([
//                'body' => utf8_encode($request->body) ,
//                'user_id' => $request->user_id,
//                'source' => 'app',
//                'parent_id' => $request->parent_id,
//                'location_id' => $location->id,
//                'specialist_id' => $followup_expert->specialist_id
//            ]);


            $user = User::find($request->user_id);

            $question = Question::create([
                'body' => utf8_encode($request->body),
                'user_id' => $request->user_id,
                'source' => 'app',
                'parent_id' => $request->parent_id,
                'location_id' => $location->i,
                'is_premium' => $user->is_premium == 1 ? 1 : 0
            ]);

        } catch (\Exception $exception) {
            return $this->makeResponse('failure', null);
        }

        return $this->makeResponse('success', $question);
    }

    public function storeFollowUpQuestion(Request $request)
    {
        try {
            $location = SetLocation::formattedLocation($request->ip(), 0, 0, $request->user_id);

            $followup_expert = FollowUp::where('question_id', $request->parent_id)->first();

            $parent_question = Question::where('id', $request->parent_id)->first();

            $user = User::find($request->user_id);

            $question = Question::create([
                'body' => utf8_encode($request->body),
                'user_id' => $request->user_id,
                'source' => 'app',
                'parent_id' => $request->parent_id,
                'location_id' => $location->id,
                'specialist_id' => $parent_question->is_premium == 0 ? $followup_expert->specialist_id : 0 ,
                'is_premium' => $user->is_premium == 1 ? 1 : 0
            ]);


            if($parent_question->is_premium == 0){
                $refer = Refer::create([
                    'question_id' => $question->id,
                    'referred_to' => $followup_expert->specialist_id,
                    'referred_by' => 25569 // system user id
                ]);
            }

            $notification_message_id = Miscellaneous::getNotificationMessageId('user_follow_up_feedback');



            if ($notification_message_id) {

                NotificationForSpecialist::createNotification($question->id, $followup_expert->specialist_id, $question->user_id, $notification_message_id);

//                NotificationSpecialists::create([
//                    'question_id' => $question->id,
//                    'notifiable' => $followup_expert->specialist_id,
//                    'notifier_id' => $question->user_id,
//                    'notification_message_id' => $notification_message_id
//                ]);
            }



        } catch (\Exception $exception) {
            return $this->makeResponse('failure', null);
        }

        return $this->makeResponse('success', $refer);
    }

    private function makeResponse($status, $data)
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'error_code' => 0,
            'error_message' => ''
        ]);
    }
}
