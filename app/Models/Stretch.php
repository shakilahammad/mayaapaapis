<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Stretch extends Model implements \Countable
{
	use SoftDeletes;

	protected $table = 'stretches';
	protected $fillable = ['user_id', 'created_at', 'updated_at', 'deleted_at', 'source'];
	protected $dates = ['deleted_at'];

	function user(){
		return $this->belongsTo(User::class);
	}

	function scopeStretchesBetween($query, $id, $after = 0, $before = null)
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

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
