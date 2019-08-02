<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GcmUser extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "gcm_users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    protected $fillable = ['gcm_id', 'user_id'];

      protected $guarded = [];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
