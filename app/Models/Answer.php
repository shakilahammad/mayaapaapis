<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Answer extends Model implements \Countable
{
	protected $table = 'answers';

	protected $fillable = ['question_id', 'body', 'user_id', 'email',  'source'];

    protected $appends = ['answerRevisionCount'] ;
    
    private $count = 0;

	public function getBodyAttribute($value){
		return utf8_decode($value);
	}

	function Question()
	{
		return $this->belongsTo(Question::class);
	}

    function AnsweredBy()
	{
		return $this->belongsTo(User::class);
	}

	function Notifications()
	{
		return $this->hasMany('Notification');
	}

	function Rating()
	{
		return $this->hasMany('Rating');
	}

	function Audit()
    {
        return $this->hasOne(Audit::class);
    }

	function scopeAnsweredByBetween($query, $id, $after = 0, $before = null)
	{
		date_default_timezone_set('Asia/Dhaka');
		if ($before == null) {
			$before = Carbon::now();
		}
		if (is_numeric($after) && $after == 0) {
			$after = Carbon::createFromTimeStamp($after);
		}
		if($id == null){
			return $query->whereBetween('created_at', array($after, $before));
		} else{
			return $query->whereUserId($id)->whereBetween('created_at', array($after, $before));
		}
	}

	function scopeAnswers($query, $id){
		return $query->whereUserId($id);
	}

    public function user()
    {
        return $this->belongsTo(User::class);
	}

    public function answerHistory()
    {
        return $this->hasMany(AnswerHistory::class);
	}

    public function getAnswerRevisionCountAttribute()
    {
        $count = $this->answerHistory()->count();
        if ($count == 0){
            return 1;
        }

        return $count;
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
