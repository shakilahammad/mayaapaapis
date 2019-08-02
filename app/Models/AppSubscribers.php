<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSubscribers extends Model implements \Countable
{
    protected $table = 'app_subscribers';

    protected $fillable = ['id', 'users_id', 'subscribers_id', 'app_subscription_plans_id', 'device_id', 'extension_info', 'status'];
    
    private $count = 0;

    public function subscribers(){
        return $this->belongsTo('App\Models\Subscribers');
    }

    public function app_subscription_plans(){
        return $this->belongsTo('App\Models\AppSubscriptionPlans');
    }

    public function users(){
        return $this->belongsTo('App\Models\Users');
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
