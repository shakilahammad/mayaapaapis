<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model implements \Countable
{
    protected $table = 'follow_ups';

    protected $fillable = ['question_id', 'specialist_id', 'notify_at', 'specialist_is_notified', 'feedback'];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function specialist()
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }

    public function followupMessages()
    {
        return $this->hasMany(FollowUpMessage::class, 'followup_id');
    }

    public function followupQuestions()
    {
        $followup_questions = FollowUpQuestion::
                                leftjoin('questions as q', 'q.id', '=', 'followup_questions.question_id')
                                ->where('followup_id', $this->id)
                                ->where('q.status', '<>', 'spam')
                                ->get(['q.id', 'q.body', 'q.source', 'q.status', 'q.user_id', 'q.is_premium', 'q.is_prescription', 'q.resolved', 'q.specialist_id']);
        return json_encode($followup_questions);
//        return $this->hasMany(FollowUpQuestion::class, 'followup_id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
