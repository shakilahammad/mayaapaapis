<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model implements \Countable
{
   protected $guarded = [];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
