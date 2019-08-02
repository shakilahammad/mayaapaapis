<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnswerHistory extends Model implements \Countable
{
    protected $table = 'answer_history';

    protected $fillable = ['question_id', 'answer_id', 'answered_by', 'answer_body', 'score', 'status', 'source'];

    private $count = 0;

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
