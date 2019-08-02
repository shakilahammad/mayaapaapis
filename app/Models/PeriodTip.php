<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PeriodTip extends Model implements \Countable
{
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
