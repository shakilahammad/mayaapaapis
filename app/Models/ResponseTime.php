<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseTime extends Model implements \Countable
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "response_time";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'question_id', 'start', 'end'];


    public function users(){
        return $this->belongsTo('App\User', 'user_id');
    }

    public function questions(){
        return $this->hasMany('App\Question', 'question_id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
