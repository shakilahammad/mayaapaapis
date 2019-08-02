<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscribers extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "subscribers";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'status', 'phone_number'
    ];

    public function tips_subscribers(){
        return $this->hasMany('App\Models\TipsSubscribers');
    }

    public function tips_activities(){
        return $this->hasMany('App\Models\TipsActivities');
    }

    public function app_subscribers(){
        return $this->hasOne('App\Models\AppSubscribers');
    }

    public function app_subscribers_garbage(){
        return $this->hasOne('App\Models\AppSubscribersGarbage');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
