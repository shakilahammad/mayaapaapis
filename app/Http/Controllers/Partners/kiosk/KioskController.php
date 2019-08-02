<?php

namespace App\Http\Controllers\Partners\Kiosk;

use Carbon\Carbon;
use App\Http\Helper;
use App\Models\User;
use App\Models\Like;
use App\Models\Rating;
use App\Models\Medium;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Classes\SetLocation;
use App\Models\TrackDownload;
use App\Models\Question_view;
use App\Models\AppSubscribers;
use Illuminate\Support\Facades\DB;
use App\Classes\MiscellaneousForApp;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class KioskController extends Controller
{
    public function loginWithPhone(Request $request)
    {
        date_default_timezone_set('Asia/Dhaka');
        $post_data = $request->input();

        $user = null;
        if (isset($post_data['phone']) && !empty($post_data['phone'])) {
//                $user_phone = ltrim($post_data['phone'], '+');
//                $user_phone = ltrim($post_data['phone'], ' ');
            $user_phone = preg_replace('/[^0-9]/', '', $post_data['phone']);
            $user_phone = '88' . $user_phone;
            $user = User::where('phone', Helper::maya_encrypt($user_phone))->first();
            $is_new = 0;
        }

        try {
            if (!$user) {
                $user = $this->registerWithPhone($post_data);
                $user = User::find($user->id);
                $is_new = 1;
            }
            if ($user) {
                //Auth::login($user);
                $user->session = 1;
                $user->save();
                $user->is_premium = AppSubscribers::whereUsersId($user->id)->exists() ? 1 : 0;
                $user->is_new = $is_new;
                $user->location = '';
                return response()->json([
                    'status' => 'success',
                    'data' => $user
                ]);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failure',
                'data' => ['exception']
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'data' => []
        ]);
    }

    public function registerWithPhone($post_data)
    {
        $this->track_user_download($post_data);
        if (isset($post_data['lat']) && isset($post_data['long']) && $post_data['long'] != 0 && $post_data['lat'] != 0) {
            $lat = $post_data['lat'];
            $long = $post_data['long'];
        } else {
            $lat = 23.991734;
            $long = 90.419588;
        }
        $location = SetLocation::formattedLocation(0, $lat, $long);
//        $phone_number = ltrim($post_data['phone'],'+');
//        $phone_number = ltrim($post_data['phone'],' ');
        $phone_number = preg_replace('/[^0-9]/', '', $post_data['phone']);
        $phone_number = '88' . $phone_number;
        $user_data['phone'] = $phone_number;
        $user_data['email'] = $phone_number . '@phone.com.bd';
        $user_data['source'] = 'kiosk';
        $user_data['session'] = 1;
        $user_data['registered'] = 1;
        $user_data['location_id'] = $location->id;

        try {
            if (isset($post_data['device_id'])) {
                $track_download_id = TrackDownload::whereDeviceId($post_data['device_id'])->first();
                if (count($track_download_id)) {
                    $user_data['track_download_id'] = $track_download_id->id;
                    //$createUser->save();
                }
            }
            $createUser = User::create($user_data);
            $location->user_id = $createUser->id;
            $location->save();
            return $createUser;

        } catch (\Exception $exception) {
            return null;
        }
    }

    public function fetchUsersQuestion(Request $request, $offset = 0, $limit = 0, $direction = 1, $order = 'DESC', $user_id = 0)
    {
         return response()->json(
             $this->fetchUsersQuestionStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id)
         );
    }

    public function fetchUsersQuestionStream($offset, $limit, $direction, $order, $user_id)
    {
        $query = "select q.*,
                       (select count(*) from likes where q.id=likes.question_id) as like_count,
                            (select count(*) from comments where q.id=comments.question_id) as 
                                  comment_count,(select count(*) from likes where 
                                        likes.user_id = $user_id and likes.question_id = q.id) as is_liked 
                                              from questions as q where q.id $direction $offset and q.user_id = $user_id
                                              and q.id not in (select question_id from question_hides where user_id = $user_id)
                                              order by q.id $order limit $limit ";
        $questions = DB::select(DB::raw($query));

        if (count($questions)) {
            return [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions)
            ];
        }

        return [
            'status' => 'failed',
            'data' => []
        ];
    }

    public function fetchRequiredData($questions)
    {
        $data = [];
        foreach ($questions as $question) {
            $values = [
                'id' => $question->id,
                'body' => html_entity_decode(utf8_decode(strip_tags($question->body))),
                'source' => $question->source == null ? 0 : $question->source,
                'status' => $question->status == null ? 0 : $question->status,
                'user_id' => $question->user_id,
                'type' => $question->type == null ? 0 : $question->type,
                'is_liked' => $question->is_liked,
                'is_premium' => $question->is_premium,
                'media_id' => $question->media_id,
                'question_created_at' => Carbon::parse($question->created_at)->diffForHumans(),
                'like_count' => $question->like_count,
                'comment_count' => $question->comment_count,
            ];
            array_push($data, $values);
        }

        return $data;
    }

    public function storeQuestion(Request $request)
    {
        $question = [
            'body' => $request->question['body'],
            'user_id' => $request->question['user_id'],
            'source' => 'kiosk',
            'is_premium' => 1
        ];

        if ($question == null) $question = [];

        $validator = Validator::make($question, [
            'body' => 'required',
            'user_id' => 'required',
            'source' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'data' => $validator->errors()
            ]);
        }

        try {
            list($lat, $long) = $this->setLatLong($request);
            $location = SetLocation::formattedLocation($request->ip(), $lat, $long, $request->question['user_id']);
            $question['location_id'] = $location->id;
            $question['body'] = utf8_encode($question['body']);
            $question['type'] = $request->question['type'] == 'audio' ? 'audio' : 'text';
            $media = $request->images;

            $qBody = $this->checkValidQuestion($question);

            if(count($qBody) > 0)
                return response()->json([
                'status' => 'Success',
                'data' => $qBody
            ]);

            if (count($media) > 0) {
                $data = Question::create($question);

                $lastMediaId = $this->storeMedia($request, $data, $media);

                $data->update([
                    'media_id' => $lastMediaId + 1
                ]);
            } else {
                $new = Question::create($question);
                $data = $new;
            }

            return response()->json([
                'status' => 'Success',
                'data' => $data
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'status' => 'failure',
                'data' => []
            ]);
        }
    }

    private function checkValidQuestion($question){
        $queue = Question::whereBody($question['body'])->where('created_at', '>', '2019-01-21')->first();

        return $queue;
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

    protected function setLatLong(Request $request)
    {
        if (!empty($request->question['lat']) && !empty($request->question['long'])) {
            $lat = $request->question['lat'];
            $long = $request->question['long'];

            return [$lat, $long];
        }

        return [0, 0];
    }

    public function getAnswer(Request $request, $question_id)
    {
        $question = Question::find($question_id);
        if (count($question)) {
            $data = $this->fetchRequiredDetails($question);
            $response = [
                'status' => 'success',
                'data' => $data
            ];
            if (isset($request->user_id)) {
                $is_viewed = Question_view::whereUserId($request->user_id)->whereQuestionId($question_id)->get();
                if (count($is_viewed) < 1) {
                    Question_view::create([
                        'user_id' => $request->user_id,
                        'question_id' => $question_id
                    ]);
                }
            }

            return response()->json($response);
        }

        return [
            'status' => 'failure',
            'data' => null
        ];
    }

    public function fetchRequiredDetails($question)
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
            $answer_created_at = Carbon::parse($question->Answer->created_at)->diffForHumans();
        } else {
            $answer_body = "Connecting to an expert. Thank you for waiting.";
            $answer_created_at = "";
        }
        $values = [
            'id' => $question->id,
            'body' => html_entity_decode(utf8_decode(strip_tags($question->body))),
            'source' => $question->source == null ? 0 : $question->source,
            'status' => $question->status == null ? 0 : $question->status,
            'user_id' => $question->user_id == null ? 0 : $question->user_id,
            'type' => $question->type == null ? 0 : $question->type,
            'is_liked' => count(Like::whereQuestionId($question->id)->whereUserId($question->user_id)->first()),
            'media_id' => $question->media_id,
            'city' => $city == null ? '' : $city,
            'country' => $country == null ? '' : $country,
            'question_created_at' => Carbon::parse($question->created_at)->diffForHumans(),
            'rating' => $rate,
            'like_count' => count($question->Likes),
            'comment_count' => count($question->Comments),
            'answer_body' => $answer_body,
            'answer_created_at' => $answer_created_at
            //'total' => $total
        ];
        return $values;
    }

    public function track_user_download($post_data)
    {
        if (isset($post_data['device_id'])) {
            $track = TrackDownload::where('device_id', $post_data['device_id'])->first();
            if (!is_null($track)) {
                $track->source = 'kiosk';
                $track->save();
            } else {
                TrackDownload::create(['device_id' => $post_data['device_id'], 'source' => 'kiosk']);
                return response()->json([
                    'status' => 'success'
                ]);
            }
        }

        return response()->json([
            'status' => 'failure'
        ]);
    }

    public function loginWithPhoneAndPassword(Request $request)
    {
        date_default_timezone_set('Asia/Dhaka');
        $post_data = $request->input();

        $user = null;
        if (isset($post_data['phone']) && !empty($post_data['phone']) && isset($post_data['password']) && !empty($post_data['password'])) {
//                $user_phone = ltrim($post_data['phone'], '+');
//                $user_phone = ltrim($post_data['phone'], ' ');
            $user_phone = preg_replace('/[^0-9]/', '', $post_data['phone']);
            $user_phone = '88' . $user_phone;

            dd(bcrypt('pass'));

            $user = User::where('phone', Helper::maya_encrypt($user_phone))->first();
            $is_new = 0;
        }

        try {
            if (!$user) {
                return response()->json([
                    'status' => 'failure',
                    'data' => null
                ]);
            }
            if ($user) {
                $user->session = 1;
                $user->save();
                $user->is_premium = AppSubscribers::whereUsersId($user->id)->exists() ? 1 : 0;
                $user->is_new = $is_new;
                $user->location = '';
                return response()->json([
                    'status' => 'success',
                    'data' => $user
                ]);
            }

        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failure',
                'data' => ['exception']
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'data' => null
        ]);
    }

    public function registerWithPhoneAndPassword(Request $post_data)
    {
        if (isset($post_data['phone']) && !empty($post_data['phone']) && isset($post_data['password']) && !empty($post_data['password'])) {
            $user_phone = preg_replace('/[^0-9]/', '', $post_data['phone']);
            $user_phone = '88' . $user_phone;
            $user_with_password = User::where('phone', Helper::maya_encrypt($user_phone))->wherePassword($post_data['password'])->first();
            $user_without_password = User::where('phone', Helper::maya_encrypt($user_phone))->first();
            if (count($user_with_password)) {
                return response()->json([
                    'status' => 'failure',
                    'reason' => 'already registered',
                    'data' => []
                ]);
            } else if (count($user_without_password)) {
                $user_without_password->password = $post_data['password'];
                $user_without_password->save();
                return response()->json([
                    'status' => 'success',
                    'reason' => 'found',
                    'data' => $user_without_password
                ]);
            } else {
                $this->track_user_download($post_data);
                if (isset($post_data['lat']) && isset($post_data['long']) && $post_data['long'] != 0 && $post_data['lat'] != 0) {
                    $lat = $post_data['lat'];
                    $long = $post_data['long'];
                } else {
                    $lat = 23.991734;
                    $long = 90.419588;
                }
                $location = SetLocation::formattedLocation(0, $lat, $long);
                $phone_number = preg_replace('/[^0-9]/', '', $post_data['phone']);
                $phone_number = '88' . $phone_number;
                $user_data['phone'] = $phone_number;
                $user_data['email'] = $phone_number . '@phone.com.bd';
                $user_data['source'] = 'kiosk';
                $user_data['password'] = $post_data['password'];
                $user_data['session'] = 1;
                $user_data['registered'] = 1;
                $user_data['location_id'] = $location->id;

                try {
                    if (isset($post_data['device_id'])) {
                        $track_download_id = TrackDownload::whereDeviceId($post_data['device_id'])->first();
                        if (count($track_download_id)) {
                            $user_data['track_download_id'] = $track_download_id->id;
                            //$createUser->save();
                        }
                    }
                    $createUser = User::create($user_data);
                    $location->user_id = $createUser->id;
                    $location->save();
                    return response()->json([
                        'status' => 'success',
                        'reason' => 'found',
                        'data' => $createUser
                    ]);
                } catch (\Exception $exception) {
                    return response()->json([
                        'status' => 'failure',
                        'reason' => $exception->getMessage(),
                        'data' => null
                    ]);
                }
            }
        }
    }

    public function reset_password(Request $post_data)
    {
        if (isset($post_data['phone']) && !empty($post_data['phone'])) {
            $user_phone = preg_replace('/[^0-9]/', '', $post_data['phone']);
            $user_phone = '88' . $user_phone;
            $user_without_password = User::where('phone', Helper::maya_encrypt($user_phone))->first();
            $user_without_password->password = $post_data['password'];
            $user_without_password->save();
            return response()->json([
                'status' => 'success',
                'reason' => 'found',
                'data' => $user_without_password
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'reason' => 'user not found',
            'data' => []
        ]);
    }

    public function storeGCM(Request $request)
    {
        if(isset($request->user_id) && isset($request->gcm_id) ) {
            $fcm = DB::table('gcm_users')
                ->where('user_id', $request->user_id)
                ->get();
            if(count($fcm)){
                DB::table('gcm_users')
                    ->where('user_id', $request->user_id)
                    ->update([
                        'gcm_id' => $request->gcm_id
                    ]);
            }else{
                DB::table('gcm_users')
                    ->where('user_id', $request->user_id)
                    ->insert([
                        'user_id' => $request->user_id,
                        'gcm_id'  => $request->gcm_id
                    ]);
            }
            $response = [
                'status' => 'success'
            ];
        }else{
            $response = [
                'status' => 'failure'
            ];
        }

        return response()->json($response);
    }

}





