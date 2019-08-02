<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model implements \Countable
{
    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function User()
    {
        return $this->belongsTo('User');
    }

    public function Question()
    {
        return $this->belongsTo('Question');
    }

    public function Answer()
    {
        return $this->belongsTo('Answer');
    }

    function scopeWithUserAndQuestion($query, $user_id, $question_id)
    {
        return $query->whereUserId($user_id)->whereQuestionId($question_id);
    }

    private $count = 0;

    public function count()
    {

        // TODO: Implement count() method.
        return ++$this->count;
    }
}
