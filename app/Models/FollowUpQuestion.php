<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUpQuestion extends Model implements \Countable
{
    protected $table = 'followup_questions';

    protected $fillable = ['question_id', 'followup_id'];

    public function followupMessage()
    {
        return $this->hasMany(FollowUpMessage::class, 'followup_id', 'followup_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
