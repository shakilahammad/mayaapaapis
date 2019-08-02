<?php

namespace App\Http\Controllers\APIs\V2;

use App\Classes\PushNotificationToUserApp;
use App\Models\FollowUp;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Question;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use App\Models\TrackDownload;
use App\Models\ScratchApplied;
use Illuminate\Support\Facades\DB;
use App\Models\AppSubscriptionPlans;
use App\Http\Controllers\Controller;

class UserMatricesController extends Controller
{
    public function getMatrices($id)
    {
        $user = User::find($id);
        if (count((array)$user) > 0) {
//            $total_question = Question::whereUserId($id)->count();
//            $total_pending = Question::whereUserId($id)->where('status', '!=', 'answered')->count();

            $totalQuestion = DB::select("select count(*) as total from questions where user_id = {$user->id}");
            $totalPending = DB::select("select count(*) as total from questions where user_id = {$user->id} and status = 'pending' ");

            $result = DB::select(DB::raw("select avg(timestampdiff(SECOND, qs.created_at, rt.end)) as response from response_time as rt, questions as qs where qs.id = rt.`question_id` and qs.`is_premium` =1 and qs.status='answered' and qs.created_at >= NOW() - INTERVAL 7 DAY"));
            $result1 = DB::select(DB::raw("select avg(timestampdiff(SECOND, qs.created_at, rt.end)) as response from response_time as rt, questions as qs where qs.id = rt.`question_id` and qs.`is_premium` =0 and qs.status='answered' and qs.created_at >= NOW() - INTERVAL 7 DAY"));

            return response()->json([
                'status' => 'success',
                'data' => [[
                    'total' => $totalQuestion[0]->total,
                    'pending' => $totalPending[0]->total,
                    'prem_response' => round($result[0]->response / 60, 2),
                    'non_prem_response' => round($result1[0]->response / 60, 2)
                ]],
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function getLastQuestionStatus($user_id)
    {
        $user = User::find($user_id);
        $totalQuestion = 0;
        $totalPending = 0;
        if (count($user)) {
            $user->update([
                'session' => 1
            ]);
//            $total_question = Question::whereUserId($user_id)->count();
//            $total_pending = Question::whereUserId($user_id)->where('status', '!=', 'answered')->count();

            $totalQuestion = DB::select("select count(*) as total from questions where user_id = {$user->id}");
            $totalPending = DB::select("select count(*) as total from questions where user_id = {$user->id} and status = 'pending' ");
            $totalFollowUp = Question::where('user_id', $user_id)
                            ->join('follow_ups as fu', 'fu.question_id', '=', 'questions.id')
                            ->count();

            $last_question = Question::whereUserId($user_id)->orderBy('created_at', 'desc')->first();

            if (count($last_question)) {
                if ($last_question->status != "answered") {
                    if ($last_question->specialist_id != 0) {
                        $type = "referred";
                    } else {
                        $type = $last_question->status;
                    }
                    $lock_queue = DB::table('locked_queue')->where('question_id', $last_question->id)->get();
                    if (count($lock_queue) > 0) {
                        $type = "locked";
                    }
                } else {
                    $type = $last_question->status;
                }
                $status = "success";
            } else {
                $status = "success";
                $type = "no question found";
            }
        } else {
            $status = "failure";
            $type = "not found";
        }
        $data = [
            'status' => $status,
            'data' => [
                'total' => $totalQuestion[0]->total,
                'pending' => $totalPending[0]->total,
                'follow_ups' => $totalFollowUp,
                'type' => $type
            ],
            'error_code' => 0,
            'error_message' => '',
        ];

        return response()->json($data);
    }

    public function formattedHistory($history, $is_active)
    {
        $planName = AppSubscriptionPlans::whereId($history->app_subscription_plans_id)->first();
        $results = [
            'id' => $history->id,
            'user_id' => $history->users_id,
            'plan' => $planName->plan_name,
            'is_active' => $is_active,
            'expiry_time' => Carbon::parse($history->updated_at)->format('d M Y  D g:i A'),
            'effective_time' => Carbon::parse($history->created_at)->format('d M Y  D g:i A'),
        ];
        return $results;
    }

    public function feedback(Request $request)
    {
        if (isset($request->feedback)) {
            $var1 = strip_tags($request->feedback);
            $var2 = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $var1), ENT_NOQUOTES, 'UTF-8');

            \Mail::raw($var2, function ($message) {
                $message->from('internal@maya.com.bd', 'User Feedback');
                $message->to('feedback@maya.com.bd', 'Feedback')->subject('New Feedback from App User!');
            });
        }
    }

    public function getLatestVersionNumberApp($package_name)
    {
        if (isset($package_name)) {
            $current_version = AppVersion::select(['version_code', 'must_update'])->where('package_name', $package_name)->get()->first();
            if ($current_version) {
                return response()->json([
                    'status' => 'success',
                    'must_update' => $current_version->must_update,
                    'version' => $current_version->version_code,
                    'error_code' => 0,
                    'error_message' => '',
                ]);
            }
        }

        return response()->json([
            'status' => 'failure',
            'version' => null,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function track_user_download(Request $request)
    {
        if (isset($request->device_id) && isset($request->source)) {
            $track = TrackDownload::where('device_id', $request['device_id'])->first();
            if (!is_null($track)) {
                $track->source = $request['source'];
                $track->save();

                return $this->makeResponse('success');
            }

            TrackDownload::create(['device_id' => $request->device_id, 'source' => $request->source]);

            return $this->makeResponse('success');

        } else if (isset($request['device_id'])) {
            TrackDownload::create(['device_id' => $request->device_id]);

            return $this->makeResponse('success');
        }

        return $this->makeResponse('failure');
    }

    public function freeMedia($user_id)
    {
        $asked_voice = Question::whereType('audio')
            ->whereUserId($user_id)
            ->whereType('audio')
            ->whereIsPremium('0')
            ->where('created_at', '>', '2017-09-07 00:00:00')->count();

        $asked_with_attachment = Question::whereUserId($user_id)
            ->whereType('text')
            ->whereIsPremium('0')
            ->where('media_id', '>', 0)
            ->where('created_at', '>', '2017-09-20 00:00:00')
            ->count();

        if (ScratchApplied::whereUserId($user_id)->exists()) {
            return response()->json([
                'status' => 'success',
                'total_voice_free' => 2,
                'total_attachemnt_free' => 2,
                'used_voice' => 0,
                'used_attachment' => 0,
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'total_voice_free' => 2,
            'total_attachemnt_free' => 2,
            'used_voice' => $asked_voice,
            'used_attachment' => $asked_with_attachment,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    private function makeResponse($status)
    {
        return response()->json([
            'status' => $status,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }


    public function sendPushToSomeUsers()
    {
        ini_set('max_execution_time', 0);
        $data = [
            "subject" => "সুখবর!",
            "message" => "message",
            "noti_type" => "custom",
            "noti_task" => "activity",
            "class_name" => "com.maya.mayaapaapp.Activities.Generic.SplashScreenActivity",
            "url" => "",
            "header_Text" => "",
            "details_text" => "",
            "btn_text" => "মায়া আপার সাথেই থাকুন",
            "log_in_needed" => 'no',
            "image_url" => "https://lh3.googleusercontent.com/-kU3nbkimpjI/W1m6ID_X-vI/AAAAAAAACxI/MZPkyhzCz8o56G6v56o7V9kA86PglV55QCL0BGAYYCw/h500/2018-07-26.png",
            "question_id" => "33",
            "article_id" => ""
        ];

        $userIds = DB::select("SELECT id FROM users WHERE id = 14228 or id = 16194");

        $ids = implode(',', collect($userIds)->pluck('id')->toArray());

        $gcmUser = DB::select(DB::raw("select gcm_id from gcm_users where user_id in($ids)"));


        $tokens = [];
        $success = 0;
        $failure = 0;
        for ($i = 0; $i < count($gcmUser); $i++) {
            array_push($tokens, $gcmUser[$i]->gcm_id);
        }


                try {
                    $post = [
                        'registration_ids' => $tokens,
                        'data' => $data
                    ];

                    echo count($tokens) . "\n";
//                    $result = PushNotificationToUserApp::sendBULKGCMNotification($post);
                    $result = PushNotificationToUserApp::sendGCMNotification($post);

//                    dd($result);

                    $result = json_decode($result, true);

//                    dd($result);

                    $s = $result['success'];
                    $f = $result['failure'];
                    $success = $success + $s;
                    $failure = $failure + $f;
                } catch (\Exception $exception) {
                    echo $exception;


        }
        echo "success: " . $success;
        echo "failure: " . $failure;
    }

}
