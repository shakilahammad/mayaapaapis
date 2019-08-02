<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorolaAppoinment extends Model implements \Countable
{
    protected $table = 'doctorola_appoinments';
    protected $fillable = ['id', 'mobile'];
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
