<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model implements \Countable {

	protected $table = 'notification_settings';
	protected $fillable = ['user_id', 'tags', 'questions', 'emails'];

	function User()
	{
		return $this->belongsTo('User');
	}

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
