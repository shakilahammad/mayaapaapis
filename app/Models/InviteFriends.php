<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteFriends extends Model implements \Countable
{
    protected $table = 'invited_friends';
    protected $fillable = ['phone', 'count_sms', 'count_visit'];
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
