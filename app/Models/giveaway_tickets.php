<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class giveaway_tickets extends Model implements \Countable
{
    protected $table = 'giveaway_tickets';

    protected $fillable = ['id', 'product_id', 'user_id','source_id','value'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
