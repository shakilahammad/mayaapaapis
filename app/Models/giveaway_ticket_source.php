<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class giveaway_ticket_source extends Model implements \Countable
{
    protected $table = 'giveaway_ticket_sources';

    protected $fillable = ['id', 'source', 'value'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
