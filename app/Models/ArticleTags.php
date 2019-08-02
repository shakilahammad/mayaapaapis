<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleTags extends Model implements \Countable
{
    protected $table = 'articles_tags';
	protected $fillable =
	[
		'id',
		'subcategory_id',
		'tag_name_en',
		'tag_name_bn',
		'tag_description'
	];
	private $count = 0;

    public function subcategories()
    {
        return $this->belongsTo('App\SubCategory');
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
