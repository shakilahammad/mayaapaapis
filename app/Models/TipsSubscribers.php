<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipsSubscribers extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "tips_subscribers";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'subscribers_id', 'tips_codes_id', 'extension_info', 'tips_id', 'tips_left'
    ];

    public function subscribers(){
        return $this->belongsTo('App\Models\Subscribers');
    }

    public function tips_codes(){
        return $this->belongsTo('App\Models\TipsCodes');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
