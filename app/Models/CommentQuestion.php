<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentQuestion extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'comment_questions';

    /**
     * By default timestamps is false
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['question_id', 'comment_id'];
    
    private $count = 0;

    /**
     * Relationship with questions's table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    /**
     * Relationship with comments's table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
