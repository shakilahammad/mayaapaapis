<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tips extends Model implements \Countable
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "tips";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'code_id', 'content'
    ];

    public function tips_codes(){
        return $this->belongsTo('App\Models\TipsCodes');
    }

    public function tips_subscribers(){
        return $this->hasMany('App\Models\TipsSubscribers');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
