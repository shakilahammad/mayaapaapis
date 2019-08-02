<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdTag extends Model implements \Countable
{
    private $count = 0;
    //
    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
