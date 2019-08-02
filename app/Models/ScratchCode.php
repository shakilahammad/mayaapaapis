<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScratchCode extends Model implements \Countable
{
    protected $table = 'scratch_codes';

    protected $fillable = ['id', 'industry_id','code'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
