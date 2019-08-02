<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Freemium extends Model implements \Countable
{
    protected $table = 'freemium';

    protected $fillable = ['id', 'invite_id', 'user_id', 'exp_date'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
