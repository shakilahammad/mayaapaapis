<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScratchIndustry extends Model implements \Countable
{
    protected $table = 'scratch_industries';

    protected $fillable = ['id', 'code','name'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
