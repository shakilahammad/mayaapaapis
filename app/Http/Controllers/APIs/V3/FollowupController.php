<?php
/**
 * Created by PhpStorm.
 * User: razib
 * Date: 2019-05-08
 * Time: 15:01
 */

namespace App\Http\Controllers\APIs\V3;

use App\Classes\Miscellaneous;
use App\Classes\NotificationForSpecialist;
use App\Models\FollowUp;
use App\Models\FollowUpQuestion;
use App\Models\Rating;
use App\Models\Refer;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Question;
use App\Models\SocioEconomicQuestion;
use App\Models\SocioEconomicUser;
use App\Classes\SetLocation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


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
//            $socio_economic_status = $this->getSocioEconomicStatus($questionId);

            return response()->json([
                'status' => 'success',
                'data' => $responseData,
                'feedback' => $followup_status,
//                'socio_economic' => $socio_economic_status,
                'error_code' => 0,
                'error_message' => ''
            ]);

//            return $this->makeResponse('success', $data);

        }catch (\Exception $exception){
            return $this->makeResponse('failure', null);
        }
    }

    public function getFollowUpList($user_id) {
        try {
            $follow_ups = DB::select(
                DB::raw(
                    "SELECT DISTINCT fu.id, fu.question_id, fu.specialist_id, fm.message_body, fu.is_seen, fu.created_at FROM followup_messages fm LEFT JOIN follow_ups fu ON fu.id = fm.followup_id LEFT JOIN questions q ON q.id = fu.question_id WHERE q.user_id = " . $user_id . " order by fu.id desc"
                )
            );
            $data = [];
            foreach ($follow_ups as $key=>$fp) {
                $expert = User::with(['profilePicture', 'specialistProfile'])->whereIn('type', ['specialist', 'admin'])->find($fp->specialist_id);
                if (!is_null($expert)) {
                    $values = [
                        'followup_id' => $fp->id,
                        'question_id' => $fp->question_id,
                        'followup_message' => $fp->message_body,
                        'expert_name' => $expert->f_name . ' ' . $expert->l_name,
                        'expert_profile_pic' => isset($expert->profilePicture) ? 'https://images-maya.s3.ap-southeast-1.amazonaws.com/images/userprofile/' . $expert->profilePicture->endpoint : 'https://images-maya.s3.ap-southeast-1.amazonaws.com/images/userprofile/1530420492.png',
                        'is_seen' => $fp->is_seen,
                        'followup_time' => $this->getFormattedTime($fp->created_at)

                    ];
                    array_push($data, $values);
                }

            }

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'error_code' => 0,
                'error_message' => ''
            ]);
        }catch (\Exception $exception){
            return $this->makeResponse('failure', null);
        }

    }

    private function getFormattedTime($time): string
    {
        return Carbon::parse($time)->diffForHumans();
    }

    private function formattedQuestion($questions)
    {
        $data = [];
        foreach($questions as $question) {
            $question->answer->q_user_id = $question->user_id;
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

            $data = [];
            $responseData = [
                'feedback_status' => !is_null($follow_up->feedback),
                'title_en' => "are you satisfied with our answer ?",
                'title_bn' => "আপনি কি আমাদের উত্তরে সন্তুষ্ট ?"
            ];
//            array_push($data, $responseData);

            return $responseData;

//            return $this->makeResponse('success', $responseData);
        }catch (\Exception $exception){
            return $this->makeResponse('failure', null);
        }

    }

    public function getSocioEconomicStatus($user_id, $lang = 'bn'){


        try{
//            $user_id = Question::find($question_id)->user_id;
//            dd(config('socio.socio_economy.3'));

            $socio_economic = SocioEconomicUser::where('user_id', $user_id)->first();
            $data = [];
            if(isset($socio_economic->ses_user_answer)) {
                $arr = json_decode($socio_economic->ses_user_answer);
//            dd($arr);
                $answered_ids = array_pluck($arr, 'id');
                $socio_questions = SocioEconomicQuestion::whereNotIn('id', $answered_ids)->inRandomOrder()->get()->take(3);

                foreach ($socio_questions as $qz) {
                    $ans_en = json_decode($qz->ses_answer_en);
                    $ans_bn = json_decode($qz->ses_answer_bn);
                    $values = [
                        'question_id' => $qz->id,
                        'question_en' => $qz->ses_question_en,
                        'question_bn' => $qz->ses_question_bn,
                        'answer_en_count' => count($ans_en),
                        'answer_bn_count' => count($ans_bn),
                        'answer_en' => $ans_en,
                        'answer_bn' => $ans_bn
                    ];
                    array_push($data, $values);
                }

                return [
                    'socio_status' => 'success',
                    'data' => $data,
                    'error_code' => 0,
                    'error_message' => ''
                ];
            } else {
                $socio_questions = SocioEconomicQuestion::inRandomOrder()->get()->take(3);

                foreach ($socio_questions as $qz) {
                    $ans_en = json_decode($qz->ses_answer_en);
                    $ans_bn = json_decode($qz->ses_answer_bn);
                    $values = [
                        'question_id' => $qz->id,
                        'question_en' => $qz->ses_question_en,
                        'question_bn' => $qz->ses_question_bn,
                        'answer_en_count' => count($ans_en),
                        'answer_bn_count' => count($ans_bn),
                        'answer_en' => $ans_en,
                        'answer_bn' => $ans_bn
                    ];
                    array_push($data, $values);
                }

                return [
                    'socio_status' => 'success',
                    'data' => $data,
                    'error_code' => 0,
                    'error_message' => ''
                ];
            }

//            return $this->makeResponse('success', $responseData);
        }catch (\Exception $exception){
            $data = [];
            return [
                'socio_status' => 'failure',
                'data' => $data,
                'error_code' => 0,
                'error_message' => ''
            ];
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

        $rating = Rating::where('user_id', $answer->q_user_id)->where('question_id', $answer->question_id)->first();

        return [
            'id' => $answer->id,
            'body' => strip_tags($answer->body),
            'rating' => isset($rating->rating) ? $rating->rating : 0,
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

    public function storeFollowUpQuestion(Request $request)
    {
        try {
            $location = SetLocation::formattedLocation($request->ip(), 0, 0, $request->user_id);

            $followup_expert = FollowUp::with(['question'])->where('question_id', $request->parent_id)->first();

            $parent_question = Question::where('id', $request->parent_id)->first();

            $user = User::find($request->user_id);

            $question = Question::create([
                'body' => utf8_encode($request->body),
                'user_id' => $request->user_id,
                'source' => 'app',
                'parent_id' => $request->parent_id,
                'location_id' => $location->id,
                'specialist_id' => $parent_question->is_premium == 0 ? $followup_expert->specialist_id : 0 ,
//                'specialist_id' => $parent_question->is_premium == 0 ? 0 : $followup_expert->specialist_id ,
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

    public function createSocioEconomicQuestion() {
        $answer_en[] = ['ans' => '৬+ বছর'];
        $answer_en[] = ['ans' => '৩-৫ বছর'];
        $answer_en[] = ['ans' => '১-২ বছর'];
        $answer_en[] = ['ans' => '১ বছরের কম'];
        $answer_en[] = ['ans' => 'বেকার'];
//        $answer_en[] = 'সিএনজি';
//        $answer_en[] = 'উবার মটো';

//        $answer_bn[] = 'পাউরুটি+মাখন/জেলি';
//        $answer_bn[] = 'কর্ন ফ্লেক্স';
//        $answer_bn[] = 'পরটা সবজি (ঘরের)';
//        $answer_bn[] = 'পরটা সবজি (বাইরের)';
//        $answer_bn[] = 'ভাত';
//        $answer_bn[] = 'সিএনজি';
//        $answer_bn[] = 'উবার মটো';

        $data = array(
            'ses_question_en' => 'আপনি কত বছর ধরে চাকরি করছেন?',
            'ses_question_bn' => 'আপনি কত বছর ধরে চাকরি করছেন?',
            'ses_answer_en' => json_encode($answer_en),
            'ses_answer_bn' => json_encode($answer_en),
            'ses_type' => 'income'
        );
//        return json_decode($data['ses_answer_en']);
        $quiz = SocioEconomicQuestion::create($data);
        dd($quiz->id);
    }

    public function updateSocioEconomicQuestion($id) {
        // update Quiz
        $quiz = SocioEconomicQuestion::find($id);
        $ans = json_decode($quiz->ses_answer_en);
//        return $quiz;
        $answer = [];
//        $i=0;
//        for ($i=0; $i<3; $i++) {
//            $answer_en['ans'] = $ans[0]->{'answer_en_'.($i+1)};
//            array_push($answer, $answer_en);
//        }
//        $quiz->ses_answer_en = json_encode($answer);
//        $ans = json_decode($quiz->ses_answer_bn);
//        $answer = [];
//        for ($i=0; $i<3; $i++) {
//            $answer_bn['ans'] = $ans[0]->{'answer_bn_'.($i+1)};
//            array_push($answer, $answer_bn);
//        }
//        $quiz->ses_answer_bn = json_encode($answer);
        $answer_en = [
            ['ans' =>'পাউরুটি+মাখন/জেলি/পরটা সবজি (ঘরের)'],
            ['ans' =>'কর্ন ফ্লেক্স'],
            ['ans' =>'পরটা সবজি (বাইরের)'],
//            ['ans' =>'পাঠাও/উবার মটো'],
            ['ans' =>'ভাত']
        ];
//        $answer_bn = [
//            ['ans' =>'মিনিপ্যাক'],
//            ['ans' =>'মিনিপ্যাক'],
//            ['ans' =>'মিনিপ্যাক'],
//            ['ans' =>'মিনিপ্যাক'],
//            ['ans' => 'বড় বোতল']
//        ];
        $quiz->ses_answer_en = json_encode($answer_en);
        $quiz->ses_answer_bn = json_encode($answer_en);
//        return json_decode($quiz->ses_answer_bn);
//        $quiz->ses_question_bn = 'আপনার পরিবারে কয়জন ভাইবোন?';
//        $quiz->ses_question_en = 'আপনার পরিবারে কয়জন ভাইবোন?';
        $quiz->save();
        return $quiz;
//        $ans[0]->ans = $ans[0]->ans1;
//        unset($ans[0]->ans1);
//        $ans[1]->ans = $ans[1]->ans2;
//        unset($ans[1]->ans2);
////        dd($ans);
////        $ans[1]->is_right = 1;
//        $quiz->answer = json_encode($ans);
//        $quiz->save();
//        return $quiz;
//        dd($quiz);
//        dd(json_decode($data[0]['answer']));
    }

    public function updateSocioEconomicStatus(Request $request){

        try{

            $req = $request->input();
            $se = SocioEconomicUser::where('user_id', $req['user_id'])->first();
            if(isset($se)) {
                $user_id = $se->user_id;
                $data = json_decode($se->ses_user_answer);
            }else {
                $user_id = $req['user_id'];
                unset($req['user_id']);
                $data = [];
            }
            array_push($data, $req);
            if(is_array($data[0])) {
                if($data[0]['id']=="1")
                    $siblings = $data[0]['answer'];
                if ($data[0]['id']=="2")
                    $location = $data[0]['answer'];
            } else {
                if($data[0]->id=="1")
                    $siblings = $data[0]->answer;
                if ($data[0]->id=="2")
                    $location = $data[0]->answer;
            }

//            else
//            dd($req['id'], $data[0]['id'], config('socio.economy'));
//            return json_encode($data['answer_id']);
//            dd($data['answer_id'], json_encode($data['answer_id']));
            $seconomic = SocioEconomicUser::updateOrCreate([
                    'user_id' => $user_id
                ],[
                    'seu_siblings' => isset($siblings) ? $siblings : 0,
                    'seu_location' => isset($location) ? $location : 0,
                    'ses_user_answer' => json_encode($data)
                ]);

            return response()->json([
                'status' => 'success'
            ]);

        }catch (\Exception $exception){
            return response()->json([
                'status' => 'failure',
                'message' => $exception->getMessage()
            ]);
        }

    }
}
