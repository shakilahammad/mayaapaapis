<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model implements \Countable {

	protected $table = 'logs';
	protected $fillable = ['ip', 'email', 'agent', 'domain', 'country', 'state', 'town', 'created_at', 'updated_at'];

	function Ip()
	{
		return $this->belongsTo('Ip');
	}

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
