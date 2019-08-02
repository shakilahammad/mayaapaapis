<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Like extends Model implements \Countable
{
	Use SoftDeletes;

	protected $table = 'likes';
	protected $fillable = ['user_id', 'comment_id', 'question_id', 'article_id','deleted_at'];
	protected $dates = ['deleted_at'];

	function User()
	{
		$this->belongsTo('App\User');
	}

	function Article()
	{
		$this->belongsTo('App\Article', 'article_id');
	}

	function Question()
	{
		$this->belongsTo('App\Question');
	}

	function Answer()
	{
		$this->belongsTo('App\Answer');
	}
	function scopeWithArticleAndUser($query, $article_id = 0, $user_id = 0)
	{
		return $query->where('article_id', '=', $article_id)->where('user_id', '=', $user_id);
	}
	function scopeWithQuestionAndUser($query, $question_id = 0, $user_id = 0)
	{
		return $query->where('question_id', '=', $question_id)->where('user_id', '=', $user_id);
	}

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
