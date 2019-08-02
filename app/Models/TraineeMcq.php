<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraineeMcq extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "trainee_mcq";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'tags_layer_two_id', 'question', 'options','correct_answer'
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
