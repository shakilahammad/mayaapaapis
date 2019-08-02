<?php

namespace App\Models;

use App\Comment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Reply
 * @package App\Models
 */
class Reply extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "reply";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'id', 'body', 'user_id', 'who', 'source', 'comment_id' ];

    /**
     * Get created at in diff human format
     * @param $value
     * @return string
     */
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }

    /**
     * Get created at in diff human format
     * @param $value
     * @return string
     */
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }

    /**
     * Relation with comments table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comments()
    {
        return $this->belongsTo(Comment::class, 'id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
