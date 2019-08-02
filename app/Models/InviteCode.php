<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model implements \Countable
{
    protected $table = 'codes';

    protected $fillable = ['id', 'code', 'type', 'referrer_id', 'title', 'exp_date'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
    
}
