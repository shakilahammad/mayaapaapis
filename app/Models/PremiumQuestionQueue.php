<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumQuestionQueue extends Model implements \Countable
{
    protected $table = 'premium_question_queue';

    protected $guarded = ['id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
