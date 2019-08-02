<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class giveaway_products extends Model implements \Countable
{
    protected $table = 'giveaway_products';

    protected $fillable = ['id', 'name', 'price','expire_date'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
