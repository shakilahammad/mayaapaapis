<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model implements \Countable {

    use SoftDeletes;

	protected $table = 'tags';

	protected $fillable = [
	    'name_en',
	    'name_bn',
	    'slug'
    ];
	protected $dates = ['deleted_at'];

    protected $hidden = ['pivot'];

    function Questions()
	{
		return $this->belongsToMany('Question', 'questions_tags', 'id', 'tag_id')->withTimestamps();
	}

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
