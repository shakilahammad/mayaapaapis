<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RamadanCheckin extends Model implements \Countable
{
    protected $table = 'ramadan_check_ins';

    protected $guarded = ['id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
