<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TopicFollow extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'following_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'tag_id'];

    /**
     * Get formatted created at
     *
     * @param $value
     * @return string
     */
    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->toFormattedDateString();
    }

    /**
     * Get formatted created at
     *
     * @param $value
     * @return string
     */
    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->toFormattedDateString();
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
