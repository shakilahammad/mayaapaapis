<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipsActivities extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "tips_activities";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'subscribers_id', 'tips_id', 'mobile_operators_id'
    ];

    public function subscribers(){
        return $this->belongsTo('App\Models\Subscribers');
    }

    public function tips(){
        return $this->belongsTo('App\Models\Tips');
    }

    public function mobile_operators(){
        return $this->belongsTo('App\Models\MobileOperators');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
