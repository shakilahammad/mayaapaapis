<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertActivityLog extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'expert_activity_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['expert_id', 'type', 'data'];

    private $count = 0;

    /**
     * Get unserialize Data
     *
     * @param $value
     * @return string
     */
    public function getDataAttribute($value)
    {
        return unserialize($value);
    }

    /**
     * Store data after serialize
     *
     * @param $value
     */
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = serialize($value);
    }


    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
