<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumIPN extends Model implements \Countable
{
    protected $table = 'premium_ipns';

    protected $guarded = ['id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
