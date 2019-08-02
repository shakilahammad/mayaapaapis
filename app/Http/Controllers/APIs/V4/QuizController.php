<?php
/**
 * Created by PhpStorm.
 * User: itsfaruque
 * Date: 4/16/19
 * Time: 11:55 AM
 */

namespace App\Http\Controllers\APIs\V4;


use App\Http\Controllers\Controller;
use App\Models\QuizUser;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Models\Quiz;

class QuizController extends Controller
{
    public function getQuiz(Request $request){

//        try {
            $device_id = $request->input('device_id') ?? '';
            $user_id = $request->input('user_id') ?? '';

            $q = new Quiz();

            $quiz = $q->get_quiz($user_id, $device_id);

            $quiz_users = $q->quiz_user($user_id, $device_id);

            if (count($quiz) == 0) {
//                $quiz_users = QuizUser::where('user_id', $user_id)->orWhere('device_id', $device_id)->get();
//                dd($quiz_users);
                if($quiz_users->count() > 0){
//                    dd($quiz_users->count());
                    foreach ($quiz_users as $qu){
                        $cat = Tag::find($qu->tag_id);
                        $last[] = [
                            'quiz_id' => $qu->quiz_group_id,
                            'quiz_tag' => $cat->name_bn,
                            'result' => $qu->result . '%'
                        ];
                    }
//                    dd(json_encode($last));
                    return response()->json([
                        'status' => 'submitted',
                        'offer_message' => '',
                        'warning_message' => 'শীঘ্রই নতুন কুইজ আসছে। আমাদের সাথেই থাকুন',
                        'category' => '',
                        'last_quiz_status_list' => $last,
                        'pass_img_url' => url('images/maya-quiz-congratulation.gif'),
                        'fail_img_url' => url('images/maya-quiz-sad.gif'),
                        'data' => []
                    ]);
                } else
                    return response()->json([
                        'status' => 'failure',
                        'data' => []
                    ]);
            } else {
                $cat = Tag::find($quiz[0]->tag_id);
                foreach ($quiz as $qz) {
                    $ans = json_decode($qz->answer);
                    $data[] = [
                        'question_id' => $qz->id,
                        'question' => $qz->question,
                        'answer_count' => count($ans),
                        'answer' => $ans
                    ];
                }

//        dd($cat->name_bn);
                if($quiz_users->count() > 0){
                    foreach ($quiz_users as $qu){
                        $cat = Tag::find($qu->tag_id);
                        $last[] = [
                            'quiz_id' => $qu->quiz_group_id,
                            'quiz_tag' => $cat->name_bn,
                            'result' => $qu->result . '%'
                        ];
                    }
                    $current_cat = Tag::find($quiz[0]->tag_id);
                    return response()->json([
                        'status' => 'success',
                        'offer_message' => '',
                        'warning_message' => '',
                        'quiz_id' => $quiz[0]->group_id,
                        'category' => $current_cat->name_bn,
                        'last_quiz_status_list' => $last,
                        'pass_img_url' => url('images/maya-quiz-congratulation.gif'),
                        'fail_img_url' => url('images/maya-quiz-sad.gif'),
                        'data' => $data
                    ]);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'offer_message' => '',
                        'warning_message' => '',
                        'quiz_id' => $quiz[0]->group_id,
                        'category' => $cat->name_bn,
                        'last_quiz_status_list' => [],
                        'pass_img_url' => url('images/maya-quiz-congratulation.gif'),
                        'fail_img_url' => url('images/maya-quiz-sad.gif'),
                        'data' => $data
                    ]);
                }
            }
//        } catch (\Exception $exception) {
//            return response()->json([
//                'status' => 'failure',
//                'data' => []
//            ]);
//        }
    }

    public function getReQuiz(Request $request){

        $device_id = $request->input('device_id') ?? '';
        $user_id = $request->input('user_id') ?? '';

        $q = new Quiz();

//        $quiz = $q->get_quiz($user_id, $device_id);

        $quiz_users = $q->quiz_user($user_id, $device_id);

        $quiz_users->delete();

        $this->getQuiz($request);
    }

    public function submitQuiz(Request $request) {
        $already_submitted = QuizUser::
            where('device_id', $request->input('device_id'))
            ->where('quiz_group_id', $request->input('quiz_id'))
            ->where('result', '<>', 0)
            ->first();
//            ->toSql();
//        dd('hello', $already_submitted);
        if(is_null($already_submitted)||count($already_submitted) == 0) {
            $quiz_user = QuizUser::create([
                'user_id' => $request->input('user_id'),
                'device_id' => $request->input('device_id'),
                'quiz_group_id' => $request->input('quiz_id'),
                'is_complete' => 1,
                'result' => $request->input('result')
            ]);

            if($request->input('result') == 0)
            {
                $quiz_user->delete();
            }
        } else {
            if($request->input('result') == 0)
            {
                $already_submitted->delete();
            }
        }
        return response()->json([
                'status' => 'success',
                'data' => []
            ]);
    }
}