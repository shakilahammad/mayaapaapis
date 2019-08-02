<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThreadLog extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'thread_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['question_id', 'expert_id', 'thread_number'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
