<?php

namespace App\Models;

use App\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Quiz extends Model implements \Countable
{
    use SoftDeletes;

    protected $table = 'quizzes';

    protected $guarded = ['id'];

    protected $user_id;

    public function tags(){
        $this->belongsTo(Tag::class, 'tag_id');
    }

    /**
     * @param $user_id
     * @param $device_id
     * @return available quiz for a user
     */
    public function get_quiz($user_id, $device_id) {
//        if(empty($user_id)) $quiz = Quiz::
////            join()
//            whereRaw('group_id not in (SELECT quiz_group_id FROM quiz_users WHERE device_id = "'.$device_id.'" and deleted_at is null)')
//            ->take(5)
//            ->get();
//        else $quiz = Quiz::
//            whereRaw('group_id not in (SELECT quiz_group_id FROM quiz_users WHERE user_id = '.$user_id.' and deleted_at is null)')
//            ->take(5)
//            ->get();

        if(!empty($device_id)) $quiz = DB::select(DB::raw("SELECT * FROM quizzes q JOIN ( SELECT (quiz_group_id + 1) AS grp_id FROM quiz_users WHERE device_id like '%".$device_id."%' AND ( deleted_at = '' OR deleted_at IS NULL ) ORDER BY quiz_group_id DESC LIMIT 1 ) d ON q.group_id = d.grp_id"));
        else $quiz = DB::select(DB::raw("SELECT * FROM quizzes q JOIN ( SELECT (quiz_group_id + 1) AS grp_id FROM quiz_users WHERE user_id = '".$user_id."' AND ( deleted_at = '' OR deleted_at IS NULL ) ORDER BY quiz_group_id DESC LIMIT 1 ) d ON q.group_id = d.grp_id"));
        if(count($quiz) == 0) $quiz = DB::select(DB::raw("select * from quizzes q where group_id = ".rand(1,6)." and group_id not in (select quiz_group_id from quiz_users where is_complete = 1 and (user_id = '%".$user_id."%' or device_id='%".$device_id."%'))"));
//        dd("SELECT * FROM quizzes q JOIN ( SELECT (quiz_group_id + 1) AS grp_id FROM quiz_users WHERE user_id = '".$user_id."' AND ( deleted_at = '' OR deleted_at IS NULL ) ORDER BY quiz_group_id DESC LIMIT 1 ) d ON q.group_id = d.grp_id");
        return $quiz;
    }

    /**
     * @param $user_id
     * @param $device_id
     * @return given quiz details of a user
     */
    public function quiz_user($user_id, $device_id) {
        if(empty($user_id)) $quiz_users = Quiz::
            leftjoin('quiz_users as qu', 'qu.quiz_group_id', '=', 'quizzes.group_id')
            ->where('qu.device_id', $device_id)
            ->groupby('qu.quiz_group_id')
            ->get(['qu.quiz_group_id', 'quizzes.tag_id', 'qu.result']);
        else $quiz_users = Quiz::
           leftjoin('quiz_users as qu', 'qu.quiz_group_id', '=', 'quizzes.group_id')
            ->whereRaw('(user_id <> "" and user_id = '. $user_id .')')
            ->groupby('qu.quiz_group_id')
            ->get(['qu.quiz_group_id', 'quizzes.tag_id', 'qu.result']);
        return $quiz_users;
    }

    public function delete_quiz_user($quiz_user_id) {
//        if(empty($user_id))
            $quiz_user = QuizUser::whereId($quiz_user_id)->first();
//        else
//            $quiz_user = \App\Models\QuizUser::whereUserId($user_id)->first();

        return $quiz_user;
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
