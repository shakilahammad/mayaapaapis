<?php

namespace App\Http\Controllers\ApiV1;

use App\Models\BotQuestion;
use App\Models\PremiumPayment;
use Carbon\Carbon;
use App\Models\Like;
use App\Models\User;
use App\Models\Rating;
use App\Models\Medium;
use App\Models\Question;
use App\Classes\SetLocation;
use Illuminate\Http\Request;
use App\Models\ActiveAppUser;
use App\Models\Question_view;
use App\Models\Question_save;
use App\Models\Question_hide;
use App\Models\AppSubscribers;
use App\Classes\Miscellaneous;
use App\Models\ScratchApplied;
use Illuminate\Support\Facades\DB;
use App\Classes\MiscellaneousForApp;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{
    public function getAnswer(Request $request, $question_id)
    {
        $question = Question::find($question_id);
        if (count($question)) {
            $data = $this->fetchRequiredDetails($question);
            try {
                $is_viewed = Question_view::whereUserId($request->user_id)->whereQuestionId($question_id)->get();
                if (count($is_viewed) < 1) {
                    Question_view::create([
                        'user_id' => $request->user_id,
                        'question_id' => $question_id
                    ]);
                }
            } catch (\Exception $exception) {
            }

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

    public function getQuestionSuggestion($question_id)
    {
        $query = "select q.id,q.source,q.body,q.created_at,q.location_id from questions as q,questions_tags as t where q.id = t.question_id and t.tag_id in ( select tag_id from questions_tags where question_id= $question_id) and char_length(q.body)>100 ORDER BY RAND() limit 2";
        $questions = DB::select(DB::raw($query));
        if (count($questions)) {
            $response = [
                'status' => 'success',
                'data' => $this->fetchSuggestedData($questions),
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

    public function fetchQuestionStream(Request $request, $offset = 0, $limit = 0, $direction = 1, $order = 'DESC', $status = null, $user_id = 0)
    {
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
    }

    public function fetchUsersQuestionStream($offset, $limit, $direction, $order, $user_id)
    {
        $questions = Question::select('*')
            ->selectRaw('(select count(*) from likes where likes.user_id = ' . $user_id . ' and likes.question_id = questions.id ) as is_liked')
            ->withCount(['likes as like_count', 'comments as comment_count'])
            ->where('id', $direction, $offset)
            ->where('user_id', $user_id)
            ->whereRaw('questions.id NOT IN (SELECT question_id FROM question_hides WHERE user_id = ' . $user_id . ')')
            ->take($limit)
            ->orderBy('id', $order)
            ->take($limit)
            ->get();
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

    public function fetchUsersQuestion($offset = 0, $limit = 0, $direction = 1, $order = 'DESC', $user_id = 0)
    {
        $user  = User::find($user_id);
        if (count($user)){
            return response()->json(
                $this->fetchUsersQuestionStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id)
            );
        }

        return response()->json([
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function hideQuestion($question_id, $user_id)
    {
        if (!Question_hide::whereQuestionId($question_id)->whereUserId($user_id)->exists()) {
            Question_hide::firstOrCreate([
                'user_id' => $user_id,
                'question_id' => $question_id,
            ]);

            return response()->json([
                'status' => 'success',
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function saveQuestion($question_id, $user_id)
    {
        if (!Question_save::whereQuestionId($question_id)->whereUserId($user_id)->exists()) {

            Question_save::firstOrCreate([
                'user_id' => $user_id,
                'question_id' => $question_id,
            ]);

            return response()->json([
                'status' => 'success',
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function fetchSavedQuestion($offset = 0, $limit = 0, $direction = 1, $order = 'DESC', $user_id = 0)
    {
        $direction = ($direction == 1) ? '>' : '<';
        $query = "select q.*,
                       (select count(*) from likes where q.id=likes.question_id) as like_count,
                            (select count(*) from comments where q.id=comments.question_id) as 
                                  comment_count,(select count(*) from likes where 
                                        likes.user_id = $user_id and likes.question_id = q.id) as is_liked 
                                              from questions as q where q.id $direction $offset
                                              and q.id not in (select question_id from question_hides where user_id = $user_id) and q.id in (select question_id from question_saves where user_id = $user_id)
                                              order by q.id $order limit $limit ";
        $questions = DB::select(DB::raw($query));
        if (count($questions)) {
            return response()->json([
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'data' => [],
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

        $botQuestion = [];

        // check if a user has bought bot package
        $premium_payment = PremiumPayment::where([
            ['user_id', '=', $user->id],
            ['package_id', '=', '10'],
            ['status', '=', 'active']
        ])->get();

//        dd($premium_payment->isEmpty());

        // make the question available in bot package
        if(!$premium_payment->isEmpty()){
            $question['status'] = 'pending';

        }

//        dd($question);
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
            $first_question_premium = Question::where('user_id', $request->question['user_id'])->exists();
            if (!$first_question_premium) {
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

        $newdata = Question::create($question);

        if(isset($question['status'])){
            $botQuestion['status'] = 'pending';
            $botQuestion['question_id'] = $newdata->id;

            BotQuestion::create($botQuestion);
        }

        if (!empty($request->images)) {
            $lastMediaId = $this->storeMedia($request, $newdata, $media);

            $newdata->update([
                'media_id' => $lastMediaId + 1,
                'type' => $request->question['type'] == 'audio' ? 'audio' : 'text'
            ]);
        }

        Miscellaneous::trackSource($newdata);

        list($responseTimeEn, $responseTimeBn) = $this->getETA($newdata, $user->id, 'question');
        
        return response()->json([
            'status' => 'Success',
            'data' => $newdata,
            'response_time_en' => $responseTimeEn,
            'response_time_bn' => $responseTimeBn,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }


    private function getETA($question, $userId, $from = 'question')
    {
        $payment = $this->getPackage($userId);

        if (count($payment)){
            $packageConfig = config("admin.package.$payment->package_id");
            if ($payment->premiumPackage->isPrescription()){
                if ($from === 'details') {
//                    return [
//                        "You will receive our call from this number {$packageConfig['phone_number']}  within {$packageConfig['average_time']} minutes",
//                        "আপনি {$packageConfig['average_time']} মিনিটের মধ্যেই এই নাম্বার {$packageConfig['phone_number']} থেকে আমাদের ফোন কল পাবেন",
//                    ];
                    return [
                        "We have received your query. An Expert will answer your query within {$packageConfig['average_time']} minutes",
                        "আপনার প্রশ্ন একজন বিশেষজ্ঞ দেখছেন। আপনি {$packageConfig['average_time']} মিনিটের মধ্যেই বিশেষজ্ঞ পরামর্শ পাবেন",
                    ];
                }

                return [
                    "We have received your query. An Expert will answer your query within {$packageConfig['average_time']} minutes",
                    "আপনার প্রশ্ন একজন বিশেষজ্ঞ দেখছেন। আপনি {$packageConfig['average_time']} মিনিটের মধ্যেই বিশেষজ্ঞ পরামর্শ পাবেন",
                ];
            }

            if ($this->checkTimeAndCount($question, $payment, $packageConfig['limit'])){
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

    protected function storeMedia($request, $newQuestionData, $media)
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

    public function parentQuestions($question_id, $user_id = 0)
    {
        $current_id = $question_id;
        $data = [];
        do {
            $questions = Question::where('id', $current_id)->orderBy('created_at', 'desc')->with('Answer')->get();
            if (count($questions) > 0) {
                foreach ($questions as $key => $question) {
                    $is_liked = count(Like::where('question_id', $question->id)->where('user_id', $user_id)->get());

                    if ($question->status == 'answered') {
                        $answer = $question->Answer;
                        if ($answer != null) {
                            $answer_body = $question->Answer->body;
                            $answer_created_at = $question->Answer->created_at->toDateTimeString();
                        } else {
                            $answer_body = 'Not Answered Yet!';
                            $answer_created_at = 0;
                        }
                    } else {
                        $answer = null;
                        $answer_body = 'Not Answered Yet!';
                        $answer_created_at = 0;
                    }
                    if ($question->id < 30) {
                        $answer_body = $question->answer;
                    }
                    if ($question->source != 'app' && $question->id > 30) {
                        $question_body = $question->body;
                    } else {
                        $question_body = $question->body;
                    }

                    list($question->area, $question->city, $question->country, $question->address) = MiscellaneousForApp::getFormattedLocation($question);

                    if (empty($question->city) && empty($question->country)) {
                        $location = 0;
                    } else {
                        $location = $question->city . ' , ' . $question->country;
                    }

                    $gender = 0;
                    $language = ($question->language != '' ? $question->language : 0);
                    $parent_id = ($question->parent_id != '' ? $question->parent_id : 0);
                    $userInfo = User::find($question->user_id)->first();
                    if (count($userInfo) > 0) {
                        $userID = $userInfo->id;
                    } else {
                        $userID = 0;
                    }
                    $is_rated = Rating::whereQuestionId($question->id)->whereUserId($user_id)->get();
                    if (count($is_rated) > 0) {
                        $rating = $is_rated[0]->rating;
                    } else {
                        $rating = 0;
                    }
                    $ratings = Rating::whereQuestionId($question->id)->get();
                    $rating_count = count($ratings);
                    $likes = $question->Likes;
                    $like_count = count($likes);
                    $avgRating = 0;
                    $question_created_at = $question->created_at->toDateTimeString();
                    foreach ($ratings as $key => $rate) {
                        $avgRating = $avgRating + $rate->rating;
                    }
                    if ($rating_count > 0) {
                        $avgRating = $avgRating / $rating_count;
                    }

                    $comments = $question->Comments;
                    foreach ($comments as $comment) {
                        $comment->comment_time = $comment->created_at->diffForHumans();
                    }
                    $comment_count = count($comments);

                    $values = [
                        'id' => $question->id,
                        'body' => $question_body,
                        'email' => $question->email,
                        'source' => $question->source,
                        'status' => $question->status,
                        'location' => $location,
                        'parent_id' => $parent_id == null ? 0 : $parent_id,
                        'user_id' => $userID == null ? 0 : $userID,
                        'gender' => $gender,
                        'language' => 0,
                        'type' => $question->type,
                        'is_liked' => $is_liked,
                        'media_id' => $question->media_id,
                        'question_created_at' => $question_created_at,
                        'rating_count' => $rating_count,
                        'avgRating' => $avgRating,
                        'rating' => $rating,
                        'like_count' => $like_count,
                        'comment_count' => $comment_count,
                        'answer_body' => $answer_body,
                        'answer_created_at' => $answer_created_at,
                    ];
                    array_push($data, $values);
                    $current_id = $question->parent_id;
                }

                $response = [
                    'status' => 'Success',
                    'data' => $data,
                    'error_code' => 0,
                    'error_message' => '',
                ];
            } else {
                $response = [
                    'status' => 'Failed',
                    'error_code' => 0,
                    'error_message' => '',
                ];
            }
        } while ($current_id != 0);

        return response()->json($response);
    }

    private function fetchPopularStream($offset, $limit, $direction, $order, $user_id)
    {
        $questions = Question::with(['answer'])
            ->withCount(['likes as like_count', 'comments as comment_count'])
            ->selectRaw('(select count(*) from likes where likes.user_id = ' . $user_id . ' and likes.question_id = questions.id ) as is_liked')
            ->where('featured', 1)
            ->where('questions.id', $direction, $offset)
            ->take($limit)
            ->whereRaw('questions.id NOT IN (SELECT question_id FROM question_hides WHERE user_id = ' . $user_id . ')')
            ->orderBy('updated_at', $order)
            ->get();
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

    private function fetchAnsweredStream($offset, $limit, $direction, $order, $user_id)
    {
        $questions = Question::selectRaw('questions.*, (select count(*) from likes where likes.question_id = id) as like_count, (select count(*) from comments where comments.question_id = id) as comment_count, (select count(*) from likes where likes.user_id = ' . $user_id . ' and likes.question_id = id) as is_liked')
            ->where('status', 'answered')
            ->where('id', $direction, $offset)
            ->where('source', '<>', 'robi')
            ->whereRaw('created_at > DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)')
            ->whereRaw('questions.id NOT IN (SELECT question_id FROM question_hides WHERE user_id = ' . $user_id . ')')
            ->take($limit)
            ->orderBy('id', $order)
            ->get();

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

    private function fetchSearchedStream($offset, $limit, $direction, $order, $user_id, $word)
    {
        $query = "select q.*,
                       (select count(*) from likes where q.id=likes.question_id) as like_count,
                            (select count(*) from comments where q.id=comments.question_id) as 
                                  comment_count,(select count(*) from likes where 
                                        likes.user_id = $user_id and likes.question_id = q.id) as is_liked 
                                              from questions as q where q.id not in (select question_id from question_hides where user_id = $user_id)
                                              and  q.id $direction $offset and
                                               q.status = 'answered' and q.body like '%{$word}%' order by q.id $order limit $limit";

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

    private function fetchRequiredData($questions)
    {
        $data = [];
        foreach ($questions as $question) {
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

    private function fetchRequiredDetails($question)
    {
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
            $answer_created_at = Carbon::parse($question->Answer->created_at)->diffForHumans();
        } else {
            $answer_body = "Connecting to an expert. Thank you for waiting.";
            $answer_body_en = "Connecting to an expert. Thank you for waiting.";
            $answer_body_bn = "আপনার প্রশ্নটি একজন বিশেষজ্ঞের কাছে পাঠানো হচ্ছে। অপেক্ষা করার জন‍্য ধন্যবাদ।";
            $answer_created_at = "";
        }
        $values = [
            'id' => $question->id,
            'body' => html_entity_decode(utf8_decode(strip_tags($question->body))),
            'source' => $question->source == null ? 0 : $question->source,
            'status' => $question->status == null ? 0 : $question->status,
            'user_id' => $question->user_id == null ? 0 : $question->user_id,
            'type' => $question->type == null ? 0 : $question->type,
            'is_liked' => Like::whereQuestionId($question->id)->whereUserId($question->user_id)->count(),
            'media_id' => $question->media_id,
            'city' => $city == null ? '' : $city,
            'country' => $country == null ? '' : $country,
            'question_created_at' => Carbon::parse($question->created_at)->diffForHumans(),
            'rating' => $rate,
            'like_count' => count($question->Likes),
            'comment_count' => count($question->Comments),
            'answer_body' => $answer_body,
            'answer_body_bn' => $answer_body_bn,
            'answer_body_en' => $answer_body_en,
            'answer_created_at' => $answer_created_at
            //'total' => $total
        ];
        return $values;
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

    public function media(Request $request)
    {
        $s3 = \Storage::disk('s3');
        $audioDestination = 'audio/questions/';
        $imageDestination = 'images/questions/';

        try {
            $question = Question::where('media_id', $request->media_id)->orderBy('id', 'desc')->first();
            $media = Medium::where('id', $request->media_id)->get();

            if ($media['0']->type == 'audio') {
                $url = $audioDestination . $question->id . '/' . $media['0']->endpoint;
                $media_url = $s3->exists($audioDestination . $question->id . '/' . $media['0']->endpoint) ? $url : 0;
            } else {
                $url = $imageDestination . $question->id . '/' . $media['0']->endpoint;
                $media_url = $s3->exists($imageDestination . $question->id . '/' . $media['0']->endpoint) == true ? $url : 0;
            }
            $array = [
                'id' => $media['0']->id,
                'media' => $media_url,
                'created_at' => Carbon::parse($media['0']->created_at)->toDateTimeString()
            ];

            return response()->json([
                'status' => 'Success',
                'data' => [$array],
                'error_code' => 0,
                'error_message' => '',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'Failed',
                'error_code' => 0,
                'error_message' => '',
            ]);
        }
    }

    public function fetchMedia($mediaId)
    {
        $media = Medium::whereId($mediaId)->get();
        if (count($media)) {
            $data = $media->map(function ($medium) {
                return [
                    'id' => $medium->id,
                    'title' => $medium->type,
                    'artist' => $medium->source,
                    'url' => $medium->endpoint
                ];
            });
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'data' => null,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    protected function storeParentQuestion(Request $request)
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

    protected function setLatLong(Request $request)
    {
        if (!empty($request->question['lat']) && !empty($request->question['long'])) {
            $lat = $request->question['lat'];
            $long = $request->question['long'];

            return [$lat, $long];
        }

        return [0, 0];
    }

}
