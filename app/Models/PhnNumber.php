<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhnNumber extends Model implements \Countable
{
    protected $table = 'phn_numbers';
    protected $fillable = ['phone', 'operator'];
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
