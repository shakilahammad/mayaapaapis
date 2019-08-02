<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model implements \Countable
{
    /**
     * @var string
     */
    protected $table = 'comments';

    /**
     * @var array
     */
    protected $guarded = ['id'];
    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    private $count = 0;

    /**
     * Relation with user table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function User()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

    /**
     * Relation with question table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function Question()
	{
		return $this->belongsTo(Question::class, 'question_id');
	}

    /**
     * Relation with answer table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function Answer()
	{
		return $this->belongsTo(Answer::class);
	}

    /**
     * Relation with reply table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reply()
    {
        return $this->hasMany(Reply::class, 'comment_id');
	}

    public function commentQuestion()
    {
        return $this->hasOne(CommentQuestion::class, 'id');
	}

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
