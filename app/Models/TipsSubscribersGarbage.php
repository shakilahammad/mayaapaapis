<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipsSubscribersGarbage extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "tips_subscribers_garbage";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'subscribers_id', 'tips_codes_id', 'extension_info', 'tips_id'
    ];

    public function subscribers(){
        return $this->belongsTo('App\Models\Subscribers');
    }

    public function tips_codes(){
        return $this->belongsTo('App\Models\TipsCodes');
    }

    public function tips_activities(){
        return $this->hasMany('App\Models\TipsActivities');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
