<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question_hide extends Model implements \Countable
{

    protected $table = 'question_hides';

    protected $fillable = ['id', 'user_id', 'question_id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
