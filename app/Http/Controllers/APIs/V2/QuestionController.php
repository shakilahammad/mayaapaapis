<?php

namespace App\Http\Controllers\APIs\V2;

use App\Models\ActiveAppUser;
use App\Models\Article;
use App\Models\Tag;
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
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function getAnswer(Request $request, $question_id)
    {
        $question = Question::find($question_id);

        if (count($question)) {

            $data = $this->fetchRequiredDetails($question);

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

        if ($request->parent_id != null) {
            $response = $this->storeParentQuestion($request);

            return response()->json($response);
        }

        $question = [
            'body' => $request->question['body'],
            'user_id' => $request->question['user_id'],
            'source' => $request->question['source'],
            'theme_type' => $request->question['question_theme_type'] ?? 0
        ];


        if ($question == null) $question = [];

        $validator = \Validator::make($question, [
            'body' => 'required',
            'user_id' => 'required',
            'source' => 'required',
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



        $question['is_premium'] = $user->is_premium == 1 ? 1 : 0;

        try {
//            $appPremium = AppSubscribers::where('users_id', $request->question['user_id'])->exists();
            $five_question_premium = Question::where('user_id', $request->question['user_id'])->get();
//            dd(count($five_question_premium));
            if ($five_question_premium->count() < 5) {
                $question['is_premium'] = 1;
            }
//            elseif (ScratchApplied::whereUserId($request->question['user_id'])->exists()) {
//                $freemium = ScratchApplied::whereUserId($request->question['user_id'])->first();
//                $now = Carbon::now();
//                if ($now->diffInDays($freemium->created_at) <= 30) {
//                    $question['is_premium'] = 1;
//                }
//            }
        } catch (\Exception $exception) {
//            \Log::error($exception->getMessage());
        }

        $location = SetLocation::formattedLocation($request->ip(), $lat, $long, $user->id);
        $question['location_id'] = $location->id;
        $question['body'] = utf8_encode($question['body']);
        $media = $request->images;

        $newQuestion = Question::create($question);

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

        return ["You will get answer within 24 hours", "আপনি উত্তর পাবেন ২৪ ঘণ্টার মধ্যেই"];
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
            $answer_body = strip_tags($question->Answer->body);
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

    public function fetchUsersQuestion($status = 1, $offset = 0, $limit = 0, $direction = 1, $order = 'DESC', $user_id = 14228)
    {
        $user  = User::find($user_id);
        if (count($user)){
            return response()->json(
                $this->fetchUsersQuestionStream(($status == 1) ? '=' : '<>', $offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id)
            );
        }

        return response()->json([
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function fetchUsersQuestionStream($status, $offset, $limit, $direction, $order, $user_id)
    {
        $questions = Question::select('*')
            ->selectRaw('(select count(*) from likes where likes.user_id = ' . $user_id . ' and likes.question_id = questions.id ) as is_liked')
            ->withCount(['likes as like_count', 'comments as comment_count'])
            ->where('status', $status, 'answered')
            ->where('id', $direction, $offset)
            ->where('user_id', $user_id)
            ->whereRaw('questions.id NOT IN (SELECT question_id FROM question_hides WHERE user_id = ' . $user_id . ')')
            ->take($limit)
            ->orderBy('id', $order)
            ->take($limit)
            ->get();

        if (count($questions) > 0 ) {

            return [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
        }
        return [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
    }

    private function fetchRequiredData($questions)
    {
        $data = [];
        foreach ($questions as $question) {

//            dd($question->tags);
            list($area, $city, $country, $address) = MiscellaneousForApp::getFormattedLocation($question);

            if (empty($country) || $country == 'Bangladesh'){
                $country = '';
            }

            $values = [
                'id' => $question->id,
                'body' => html_entity_decode(utf8_decode(strip_tags($question->body))),
                'source' => $question->source == null ? 0 : $question->source,
                'status' => $question->status == null ? 0 : $question->status,
                'user_id' => $question->user_id,
                'type' => $question->type == null ? 0 : $question->type,
                'tags' => isset($question->tags) ? $question->tags : null,
//                'city' => $city == null ? '' : $city,
//                'country' => $country == null ? '' : $country,
                'city' => $city == null ? '' : trim(str_replace('District', '', $city)),
                'country' => $country,
                'is_liked' => $question->is_liked,
                'is_premium' => $question->is_premium,
                'media_id' => $question->media_id,
                'question_created_at' => Carbon::parse($question->created_at)->diffForHumans(),
                'like_count' => $question->like_count,
                'comment_count' => $question->comment_count,
                'question_theme_type' => $question->theme_type ?? 0

            ];
            array_push($data, $values);
        }

        return $data;
    }

    /**
     * Suggested question & article
     */

    public function getQuestionSuggestion($lang, $question_id)
    {
        $lang = 'bn';
        $query = "select q.id,q.source,q.body,q.created_at,q.location_id, t.tag_id from questions as q,questions_tags as t where q.id = t.question_id and t.tag_id in ( select tag_id from questions_tags where question_id= $question_id) and char_length(q.body)>100 ORDER BY RAND() limit 2";
        $questions = DB::select(DB::raw($query));
        $tag = Tag::whereId($questions[0]->tag_id)->first(['name_'.$lang]);
        if (count($questions)) {
            $response = [
                'status' => 'success',
                'data' => $this->fetchSuggestedData($questions),
                'article' => $this->fetchSuggestedArticle($tag->{'name_'.$lang}),
                'error_code' => 0,
                'error_message' => '',
            ];
            return $response;
        }

        $response = [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];


        return $response;
    }

    private function fetchSuggestedData($questions)
    {
        $data = [];
        foreach ($questions as $question) {
            list($area, $city, $country, $address) = MiscellaneousForApp::getFormattedLocation($question);
            $values = [
                'id' => $question->id,
                'body' => html_entity_decode(utf8_decode(strip_tags($question->body))),
                'source' => $question->source == null ? 0 : $question->source,
                'city' => $city == null ? '' : $city,
                'country' => $country == null ? '' : $country,
                'question_created_at' => Carbon::parse($question->created_at)->diffForHumans(),

            ];
            array_push($data, $values);
        }

        return $data;
    }

    private function fetchSuggestedArticle($tag_name)
    {

        $articles = new Article();
        $data = $articles->searchArticles(0, 2, $tag_name);

        return $data;
    }

    public function fetchQuestionStream(Request $request, $offset = 0, $limit = 0, $direction = 1, $order = 'DESC', $status = null, $user_id = 0)
    {
        try{
            if (isset($user_id) && !empty($user_id)) {
                $this->storeActivity($user_id);
            }
            switch ($request->type) {
                case 'answered':
                    if ($status == 'default') {
                        if (isset($request['text'])) {
                            return response()->json($this->fetchSearchedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['text']));
                        } else if (isset($request['date'])) {
                            return response()->json($this->fetchDatedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['date']));
                        } else if (isset($request['tag'])) {
                            return response()->json($this->fetchTaggedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['tag']));
                        } else {
                            return response()->json($this->fetchAnsweredStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id));
                        }
                    } else {
                        return response()->json($this->fetchPopularStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id));
                    }
                    break;
                case 'pending':
                    if ($status == 'default') {
                        if (isset($request['text'])) {
                            return response()->json($this->fetchSearchedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['text']));
                        } else if (isset($request['date'])) {
                            return response()->json($this->fetchDatedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['date']));
                        } else if (isset($request['tag'])) {
                            return response()->json($this->fetchTaggedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['tag']));
                        } else {
                            return response()->json($this->fetchPendingStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id));
                        }
                    } else {
                        return response()->json($this->fetchPopularStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id));
                    }
                    break;
            }
        }catch (\Exception $exception){

            $data = [
                'status' => 'failure',
                'message' => $exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine()
            ];

            return response()->json($data);
        }
    }

    private function storeActivity($user_id)
    {
        try {
            $user = User::find($user_id);
            $user->update([
                'session' => 1
            ]);
        } catch (\Exception $exception) {

        }

        $records = ActiveAppUser::whereUserId($user_id)->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])->first();

        if (count($records)) {

            $records->update([
                'updated_at' => Carbon::now()
            ]);
        } else {
            ActiveAppUser::create([
                'user_id' => $user_id
            ]);
        }
    }

    private function fetchSearchedStream($offset, $limit, $direction, $order, $user_id, $word)
    {
//        $query = "select q.*,
//                       (select count(*) from likes where q.id=likes.question_id) as like_count,
//                            (select count(*) from comments where q.id=comments.question_id) as
//                                  comment_count,(select count(*) from likes where
//                                        likes.user_id = $user_id and likes.question_id = q.id) as is_liked
//                                              from questions as q where q.id not in (select question_id from question_hides where user_id = $user_id)
//                                              and  q.id $direction $offset and
//                                               q.status = 'answered' and q.body like '%{$word}%' order by q.id $order limit $limit";

//        $questions = DB::select(DB::raw($query));

//        $questions = User::modelsFromRawResults($questions);

        $questions = Question::with(['tags'])
            ->withCount(['likes as like_count', 'comments as comment_count'])
            ->selectRaw('(select count(*) from likes where likes.user_id = ' . $user_id . ' and likes.question_id = questions.id ) as is_liked')
//            ->where('questions.id', $direction, $offset)
//            ->whereRaw('questions.id NOT IN (SELECT distinct question_id FROM question_hides WHERE user_id = ' . $user_id . ')')
            ->whereNotExists(function ($query) use ($user_id){
                $query->select(DB::raw(1))
                    ->from('question_hides')
                    ->where('user_id', $user_id)
                    ->where('question_id', '=', 'questions.id');
            })
            ->where('questions.status', '=', 'answered' )
            ->where('questions.body', 'LIKE', '%'.$word.'%')
            ->orderBy('questions.id', $order)
            ->take($limit)
            ->get();


//        dd($questions);


        if (count($questions)) {

            return [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
        }

        return [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
    }

    private function fetchDatedStream($offset, $limit, $direction, $order, $user_id, $date)
    {
        $query = "select q.*,
                       (select count(*) from likes where q.id=likes.question_id) as like_count,
                            (select count(*) from comments where q.id=comments.question_id) as 
                                  comment_count,(select count(*) from likes where 
                                        likes.user_id = $user_id and likes.question_id = q.id) as is_liked 
                                              from questions as q where q.id $direction $offset and q.status = 'answered'
						and q.created_at > DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)
                                                and q.id not in (select question_id from question_hides where user_id = $user_id)
                                                and q.created_at like '%{$date}%' order by q.id $order limit $limit";
        $questions = DB::select(DB::raw($query));
        if (count($questions)) {
            return [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
        }

        return [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
    }

    private function fetchTaggedStream($offset, $limit, $direction, $order, $user_id, $tag)
    {
        $tag_names = $this->getTagNames($tag);
        $query1 = "select id from tags where name_en regexp $tag_names";
        $tags = DB::select(DB::raw($query1));
        $tag_ids = '';
        for ($i = 0; $i < count($tags); $i++) {
            if ($i != count($tags) - 1) {
                $tag_ids .= $tags[$i]->id . ',';
            } else {
                $tag_ids .= $tags[$i]->id;
            }
        }

        $query = "select q.*,
                       (select count(*) from likes where q.id=likes.question_id) as like_count,
                            (select count(*) from comments where q.id=comments.question_id) as 
                                  comment_count,(select count(*) from likes where 
                                        likes.user_id = 0 and likes.question_id = q.id) as is_liked 
                                              from questions as q where q.id $direction $offset and
                                                                 q.id not in (select question_id from question_hides where user_id = $user_id) 
                                                              and q.id in (select question_id from questions_tags where tag_id in ($tag_ids))
                                                              
                                                              order by q.id desc limit $limit";
        $questions = DB::select(DB::raw($query));
        if (count($questions)) {
            return [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
        }
        return [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
    }

    private function getTagNames($name)
    {
        switch ($name) {
            case 'General Health':
                return '"' . "Breast Diseases|Before Pregnancy|Women's Health|Health|Nutrition|Men's Health|Mental Health|Teen Health|Cancer & Terminal Diseases" . '"';
                break;
            case 'Sex Education':
                return '"' . "Sex Education|Contraception & Family Planning|Menstruation|STIs" . '"';
                break;
            case 'Pregnancy':
                return '"' . "Getting Pregnant|Pregnancy|Post Pregnency" . '"';
                break;
            case 'Lifestyle':
                return '"' . "Beauty & Skin Care|Fitness & Well-being|Dermatology" . '"';
                break;
            case 'Parenting Children':
                return '"' . "Baby care & Vaccination|Parenting & Breastfeeding|Parenting & Parenthood" . '"';
                break;
            case 'Social Issues':
                return '"' . "Sexual Harrassment at Workplace|Social Issues|Psychosocial|Legal|Domestic Violence/ Violence at Workplace|Divorce & Child Support|Psychosocial|Drugs & Substance Abuse" . '"';
                break;
            case'Others':
                return '"' . "Other|General Info" . '"';
                break;
        }
    }

    private function fetchAnsweredStream($offset, $limit, $direction, $order, $user_id)
    {
        $questions = Question::with(['Tags'])->selectRaw('questions.*, (select count(*) from likes where likes.question_id = id) as like_count, (select count(*) from comments where comments.question_id = id) as comment_count, (select count(*) from likes where likes.user_id = ' . $user_id . ' and likes.question_id = id) as is_liked')
            ->where('status', 'answered')
            ->where('id', $direction, $offset)
            ->where('source', '<>', 'robi')
            ->whereRaw('created_at > DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)')
            ->whereRaw('questions.id NOT IN (SELECT question_id FROM question_hides WHERE user_id = ' . $user_id . ')')
            ->take($limit)
            ->orderBy('id', $order)
//            ->toSql();
            ->get();

//        dd($questions);


        if (count($questions)) {

            $response = [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
            return $response;
        } else {
            $response = [
                'status' => 'failed',
                'data' => [],
                'error_code' => 0,
                'error_message' => '',
            ];
            return $response;
        }
    }

    private function fetchPopularStream($offset, $limit, $direction, $order, $user_id)
    {
        $questions = Question::with(['tags','answer'])
            ->withCount(['likes as like_count', 'comments as comment_count'])
            ->selectRaw('(select count(*) from likes where likes.user_id = ' . $user_id . ' and likes.question_id = questions.id ) as is_liked')
            ->where('featured', 1)
            ->where('questions.id', $direction, $offset)
            ->take($limit)
            ->whereRaw('questions.id NOT IN (SELECT question_id FROM question_hides WHERE user_id = ' . $user_id . ')')
            ->orderBy('updated_at', $order)
            ->get();
//        dd($questions);


        if (count($questions)) {

            $response = [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
            return $response;
        }
        $response = [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
        return $response;
    }

    private function fetchPendingStream($offset, $limit, $direction, $order, $user_id)
    {
        $query = "select q.*,
                       (select count(*) from likes where q.id=likes.question_id) as like_count,
                            (select count(*) from comments where q.id=comments.question_id) as 
                                  comment_count,(select count(*) from likes where 
                                        likes.user_id = $user_id and likes.question_id = q.id) as is_liked 
                                              from questions as q where q.id $direction $offset
                                              and q.id not in (select question_id from question_hides where user_id = $user_id) and
                                              q.status = 'pending' order by q.id $order limit $limit ";
        $questions = DB::select(DB::raw($query));

        if (count($questions)) {
            return [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
        }

        return [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
    }


}
