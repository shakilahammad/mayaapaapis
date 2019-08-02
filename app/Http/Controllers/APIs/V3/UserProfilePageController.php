<?php

namespace App\Http\Controllers\APIs\V3;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserProfilePageController extends Controller{


    public function getLastQuestionStatus($user_id)
    {
        $user = User::find($user_id);
        $total_point =0;
        $user_badge_name ="";
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

            $user_pont_data=DB::table("user_points")->where("user_id",'=', $user_id)->get();
            $total_point = 0;
            if($user_pont_data->count()==0){
                $total_point =0;
            }
            else{
                $total_point=$user_pont_data[0]->total_points;
                $user_badge_info =  $user_badge = DB::table("point_user_badges")
                    ->join("point_badges", "point_user_badges.badge_id","=", "point_badges.id")
                    ->where("user_id", $user_id)
                    ->get();

                if ($user_badge_info->count()==0){
                    $user_badge_name = "Normal";
                }
                else{
                    $user_badge_name=$user_badge_info[0]->name;
                }
            }


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
                'total_points'=>$total_point,
                'user_current_badge_name'=>$user_badge_name,
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
}