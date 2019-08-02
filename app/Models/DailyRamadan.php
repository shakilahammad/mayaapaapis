<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyRamadan extends Model implements \Countable
{
    protected $table = 'daily_ramadan';

    protected $guarded = 'id';
    
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
