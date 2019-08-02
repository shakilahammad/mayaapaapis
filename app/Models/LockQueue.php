<?php

namespace App\Models;

use App\Classes\Miscellaneous;
use Illuminate\Database\Eloquent\Model;

class LockQueue extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "locked_queue";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['specialist_id', 'question_id', 'created_at'];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public static function getPremiumAnsweringNow()
    {
        $locked_questions = LockQueue::all();
        $answeringNow = [];
        foreach ($locked_questions as $locked_question) {
            if($locked_question->question->is_premium == true && $locked_question->question->status == 'pending'){
                array_push($answeringNow, $locked_question->question);
            }
        }

        return count($answeringNow) ? Miscellaneous::getFormattedUnAnsweredQuestions($answeringNow) : null;
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
