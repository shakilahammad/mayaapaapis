<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spam extends Model implements \Countable
{
     /**
     * The database table used by the model.
     *
     * @var string
     */
     protected $table = "spams";

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     protected $guarded = ['id'];

     public $timestamps = true;

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
