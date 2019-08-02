<?php
/**
 * Created by PhpStorm.
 * User: razib
 * Date: 2019-07-03
 * Time: 17:24
 */

namespace App\Http\Controllers\Partners\POCO;

use App\Models\PocoQuestion;
use Carbon\Carbon;
use App\Models\Like;
use App\Models\User;
use App\Models\Rating;
use App\Models\Medium;
use App\Models\Question;
use App\Classes\SetLocation;
use Illuminate\Http\Request;
use App\Models\Question_view;
use App\Models\PremiumPayment;
use App\Classes\Miscellaneous;
use App\Models\SpecialistProfile;
use App\Classes\MiscellaneousForApp;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{

    public function getAnswer(Request $request, $question_id)
    {
        $question = Question::find($question_id);
        if (count($question)) {
            $data = $this->fetchRequiredDetails($question);
//            dd($data);
            $this->storeQuestionView($request, $question_id);

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'data' => null,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function storeQuestion(Request $request)
    {

        list($lat, $long) = $this->setLatLong($request);
        $user = User::find($request->question['user_id']);
        $session = $request->question['session'];

        if ($request->parent_id != null) {
            $response = $this->storeParentQuestion($request);

            return response()->json($response);
        }



        $question = [
            'body' => $request->question['body'],
            'user_id' => $request->question['user_id'],
            'source' => $request->question['source'],
            'theme_type' => $request->question['question_theme_type'] ?? 0,
            'redirect_type' => $request->question['redirect_type'] ?? null
        ];


        if ($question == null) $question = [];

        $validator = \Validator::make($question, [
            'body' => 'required',
            'user_id' => 'required',
            'source' => 'required',
            'session' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'data' => $validator->errors(),
                'response_time_en' => '',
                'response_time_bn' => '',
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        $five_question_premium = Question::where('user_id', $request->question['user_id'])->get();
//            dd(count($five_question_premium));
        if ($five_question_premium->count() < 5) {
            $question['is_premium'] = 1;
        }else{
            $question['is_premium'] = $user->is_premium == 1 ? 1 : 0;
        }

        $location = SetLocation::formattedLocation($request->ip(), $lat, $long, $user->id);
        $question['location_id'] = $location->id;
        $question['body'] = utf8_encode($question['body']);
        $media = $request->images;
        $newQuestion = Question::create($question);

        $this->storeSession($newQuestion->user_id, $newQuestion->id, $request->question['session']);

        if (!empty($request->images)) {
            $lastMediaId = $this->storeMedia($request, $newQuestion, $media);

            $newQuestion->update([
                'media_id' => $lastMediaId + 1,
                'type' => $request->question['type'] == 'audio' ? 'audio' : 'text'
            ]);
        }

        Miscellaneous::trackSource($newQuestion);

        list($responseTimeEn, $responseTimeBn) = $this->getETA($newQuestion, $user->id, 'question');

        return response()->json([
            'status' => 'Success',
            'data' => $newQuestion,
            'response_time_en' => $responseTimeEn,
            'response_time_bn' => $responseTimeBn,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    private function storeSession($user_id, $question_id, $session){

        try{

            PocoQuestion::create([
                'user_id' => $user_id,
                'question_id' => $question_id,
                'session' => $session
            ]);

        }catch (\Exception $exception){

            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error_code' => 0,
                'error_message' => '',
            ]);

        }

    }

    private function getETA($question, $userId, $from = 'question')
    {
        $payment = $this->getPackage($userId);

        if (count($payment)) {
            $packageConfig = config("admin.package.$payment->package_id");
            if ($payment->premiumPackage->isPrescription()) {
                if ($from === 'details') {
                    return [
                        "You will receive our call from this number {$packageConfig['phone_number']}  within {$packageConfig['average_time']} minutes",
                        "আপনি {$packageConfig['average_time']} মিনিটের মধ্যেই এই নাম্বার {$packageConfig['phone_number']} থেকে আমাদের ফোন কল পাবেন",
                    ];
                }

                return [
                    "We have received your query. You will receive our call from this number {$packageConfig['phone_number']}  within {$packageConfig['average_time']} minutes",
                    "আপনার প্রশ্ন জমা হয়েছে। আপনি {$packageConfig['average_time']} মিনিটের মধ্যেই এই নাম্বার {$packageConfig['phone_number']} থেকে আমাদের ফোন কল পাবেন",
                ];
            }

            if ($this->checkTimeAndCount($question, $payment, $packageConfig['limit'])) {
                return [
                    "You will get answer within {$packageConfig['minute']} minutes",
                    "আপনি উত্তর পাবেন {$packageConfig['minute']} মিনিটের মধ্যেই"
                ];
            }
        }

        return ["Your question has been reffered to an expert. Soon the expert will answer your question. Please bear with us.", "আপনার প্রশ্নটি একজন এক্সপার্টের কাছে রেফার হয়েছে। এক্সপার্ট দ্রুতই আপনার প্রশ্নের উত্তর দিবেন। আমাদের সাথেই থাকুন।"];
    }

    private function getPackage($userId)
    {
        return PremiumPayment::with(['premiumPackage'])->whereUserId($userId)->whereStatus('active')->first();
    }

    private function checkTimeAndCount($question, $package, $count)
    {
        $start = '08:00:00';
        $end = '20:00:00';
        $time = $question->created_at->format('H:i:s');
        $questionCount = \DB::select("SELECT count(*) as count FROM  questions WHERE user_id = {$question->user_id} AND created_at BETWEEN '{$package->effective_time}' AND '{$package->getOriginal('expiry_time')}' AND HOUR(created_at) BETWEEN 08 AND 20");

        return $questionCount[0]->count <= $count && $time >= $start && $time <= $end;
    }

    private function storeMedia($request, $newQuestionData, $media)
    {
        $s3 = \Storage::disk('s3');
        $audioDestination = 'audio/questions/';
        $imageDestination = 'images/questions/';

        $lastMediaId = Medium::orderBy('id', 'desc')->first()->id;

        foreach ($media as $key => $medium) {
            $fileName = time() . '' . rand(1, 1000) . '.' . $medium->getClientOriginalExtension();
            $post_data['images'] = $fileName;
            if ($request->question['type'] == "audio") {
                $s3->put($audioDestination . $newQuestionData->id . '/' . $fileName, file_get_contents($medium));
            } else {
                $s3->put($imageDestination . $newQuestionData->id . '/' . $fileName, file_get_contents($medium));
            }

            Medium::create([
                'id' => $lastMediaId + 1,
                'endpoint' => $fileName,
                'source' => $request->question['source'],
                'type' => $request->question['type'] == 'audio' ? 'audio' : 'image'
            ]);
        }

        return $lastMediaId;
    }

    private function fetchRequiredDetails($question)
    {
        $answeredBy = '';
        $qualification = '';
        list($area, $city, $country, $address) = MiscellaneousForApp::getFormattedLocation($question);
        $user_id = $question->user_id;
        if (!count($user_id) > 0) {
            $question->user_id = 0;
        }

        $rating = Rating::whereQuestionId($question->id)->whereUserId($question->user_id)->first();
        if (count($rating)) {
            $rate = $rating->rating;
        } else {
            $rate = 0;
        }


        if (count($question->Answer)) {
//            $answer_body = strip_tags($question->Answer->body);
            $answer_body = $question->Answer->body;
            $answer_body_en = '';
            $answer_body_bn = '';
            $answer_created_at = $this->getFormattedTime($question->Answer->created_at);
            if ($question->isPrescription()) {
                list($answeredBy, $qualification) = $this->answeredBy($question->answer);
//                if ($question->status == 'pending') {
//                    list($answer_body_en, $answer_body_bn) = $this->getETA($question, $question->user_id, 'details');
//                }
            }

            $expertProfile = $this->getProfile($question->answer);

        } else {
            $answer_body = "Connecting to an expert. Thank you for waiting.";
            $answer_body_en = "Connecting to an expert. Thank you for waiting.";
            $answer_body_bn = "আপনার প্রশ্নটি একজন বিশেষজ্ঞের কাছে পাঠানো হচ্ছে। অপেক্ষা করার জন‍্য ধন্যবাদ।";
            $answer_created_at = "";
        }

        if ($question->isPrescription() && $question->status == 'pending') {
            list($answer_body_en, $answer_body_bn) = $this->getETA($question, $question->user_id, 'details');
        }

        $qualification = $question->isPrescription() ? $qualification : $expertProfile->specialistProfile->qualification ?? '';

        $values = [
            'id' => $question->id,
            'body' => html_entity_decode(utf8_decode(strip_tags($question->body))),
            'source' => $question->source ?? 0,
            'status' => $question->status ?? 0,
            'user_id' => $question->user_id ?? 0,
            'type' => $question->type ?? 0,
            'is_prescription' => $question->is_prescription,
            'has_prescription' => $question->Prescription()->first() ? true : false,
            'is_liked' => Like::whereQuestionId($question->id)->whereUserId($question->user_id)->count(),
            'media_id' => $question->media_id,
            'city' => $city ?? '',
            'country' => $country ?? '',
            'question_created_at' => $this->getFormattedTime($question->created_at),
            'rating' => $rate,
            'like_count' => count($question->Likes),
            'comment_count' => count($question->Comments),
            'answer_body' => $answer_body,
            'answered_by' => $answeredBy,
            'qualification' => $qualification,
            'answer_body_bn' => $answer_body_bn,
            'answer_body_en' => $answer_body_en,
            'answer_created_at' => $answer_created_at,
            'specialist_profile_picture' => $expertProfile->profilePicture->url ?? '',
            'specialist_id' => $expertProfile->specialistProfile->specialist_id ?? '',
            'specialist_name' => $expertProfile->specialistProfile->shadow_name ?? ''
            //'total' => $total
        ];

        return $values;
    }

    private function storeParentQuestion($request)
    {
        list($lat, $long) = $this->setLatLong($request);

        $location = SetLocation::formattedLocation($request->ip(), $lat, $long, $request->question['user_id']);
        $question = Question::create([
            'body' => $request->question['body'],
            'user_id' => $request->question['user_id'],
            'source' => $request->question['source'],
            'location_id' => $location->id,
            'parent_id' => $request->parent_id,
            'theme_type' => $request->question['question_theme_type'] ?? 0
        ]);

        Miscellaneous::trackSource($question);

        return [
            'status' => 'Success',
            'data' => $question
        ];
    }

    private function setLatLong($request)
    {
        if (!empty($request->question['lat']) && !empty($request->question['long'])) {
            $lat = $request->question['lat'];
            $long = $request->question['long'];

            return [$lat, $long];
        }

        return [0, 0];
    }

    private function storeQuestionView(Request $request, $question_id): void
    {
        try {
            $viewCount = Question_view::whereUserId($request->user_id)->whereQuestionId($question_id)->first();
            if (count($viewCount) < 1) {
                Question_view::create([
                    'user_id' => $request->user_id,
                    'question_id' => $question_id
                ]);
            }
            elseif(count($viewCount) == 1){

                $viewCount->update([
                    'updated_at' => Carbon::now()
                ]);
            }
        } catch (\Exception $exception) {
        }
    }

    private function getFormattedTime($time): string
    {
        return Carbon::parse($time)->diffForHumans();
    }

    private function answeredBy($answer)
    {
        $expertProfile = SpecialistProfile::withTrashed()->where('specialist_id', $answer->user_id)->first();

        return [
            $expertProfile->shadow_name ?? '',
            $expertProfile->qualification ?? '',
        ];
    }

    private function  getProfile($answer)
    {
        $profile = User::with(['specialistProfile', 'profilePicture'])->find($answer->user_id);

        return $profile;
    }
}
