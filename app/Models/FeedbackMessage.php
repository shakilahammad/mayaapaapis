<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackMessage extends Model implements \Countable
{
    protected $table = "feedback_messages";

    protected $fillable = ['body_en', 'body_bn'];

    public function feedbackMessage()
    {
        return $this->belongsTo(Feedback::class);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
