<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileOperators extends Model implements \Countable
{
    protected $table = 'mobile_operators';

    protected $fillable = ['id', 'operator_name'];

    public function app_subscription_plans(){
        return $this->belongsTo('App\Models\AppSubscriptionPlans');
    }

    public function tips_activities(){
        return $this->hasMany('App\Models\Tips');
    }

    public function mobile_operators_logs(){
        return $this->hasMany('App\Models\MobileOperatorsLogs');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
