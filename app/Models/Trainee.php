<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainee extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "trainee";
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','q_id','user_id','result'
    ];
    /**
     * Get created at in diff human format
     * @param $value
     * @return string
     */
    // public function getOptionsAttribute($value)
    // {
    //     $this->attributes['options'] = serialize($value); 
    // }
    // public function setOptionsAttribute($value) 
    // { 
    //     $this->attributes['options'] = unserialize($value); 
    // }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
