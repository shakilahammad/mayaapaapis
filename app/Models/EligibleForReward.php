<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EligibleForReward extends Model implements \Countable
{
    protected $table = 'reward_eligible_users';

    protected $fillable = ['user_id', 'message_id', 'is_eligible', 'exp_date'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
