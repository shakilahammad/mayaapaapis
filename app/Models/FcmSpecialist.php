<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FcmSpecialist extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "fcm_specialists";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['specialist_id', 'fcm_id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
