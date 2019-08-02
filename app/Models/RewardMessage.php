<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardMessage extends Model implements \Countable
{
    protected $table = 'reward_message';

    protected $fillable = ['message', 'exp_date'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
