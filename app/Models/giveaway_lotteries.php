<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class giveaway_lotteries extends Model implements \Countable
{
    protected $table = 'giveaway_lotteries';

    protected $fillable = ['id', 'user_id', 'product_id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
