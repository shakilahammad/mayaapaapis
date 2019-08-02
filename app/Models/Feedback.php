<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model implements \Countable
{
    protected $table = "feedbacks";

    protected $casts = [
        'is_helpfull' => 'boolean',
    ];

    protected $fillable = ['question_id', 'user_id', 'feedback_message_id', 'is_helpfull', 'source'];

    public function feedbackMessage()
    {
        return $this->hasOne(FeedbackMessage::class);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
