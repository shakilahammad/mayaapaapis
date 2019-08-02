<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumLogs extends Model implements \Countable
{
    protected $table = 'premium_logs';

    protected $guarded = ['id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
