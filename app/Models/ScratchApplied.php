<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScratchApplied extends Model implements \Countable
{
    protected $table = 'scratch_applied';

    protected $fillable = ['id', 'code_id','user_id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
