<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicCard extends Model implements \Countable
{
    protected $table = 'dynamic_card';

    protected $guarded = ['id'];
    
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
