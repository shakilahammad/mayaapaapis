<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotificationUsers extends Model implements \Countable
{
    protected $guarded = ['id'];

//    function message(){
//        $this->hasMany(PushNotificationMessage::class);
//    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
