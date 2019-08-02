<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PeriodUsersMode extends Model implements \Countable
{
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
