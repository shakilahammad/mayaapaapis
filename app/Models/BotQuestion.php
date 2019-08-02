<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BotQuestion extends Model implements \Countable
{
    //

    use SoftDeletes;

    protected $table = 'bot_questions';

    protected $guarded = ['id'];

    private $count = 0;


    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
