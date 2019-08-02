<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAnswerLog extends Model implements \Countable
{
    protected $table = 'ai_answer_logs';

    protected $guarded = ['id'];
    
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
