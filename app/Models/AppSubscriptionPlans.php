<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSubscriptionPlans extends Model implements \Countable
{
    protected $table = 'app_subscription_plans';
    protected $fillable = ['plan_name', 'mobile_operators_id', 'plan_type'];
    private $count = 0;

    public function mobile_operators(){
        return $this->belongsTo('App\Models\MobileOperators');
    }

    public function app_subscribers(){
        return $this->hasMany('App\Models\AppSubscribers');
    }

    public function app_subscribers_garbage(){
        return $this->hasOne('App\Models\AppSubscribersGarbage');
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
