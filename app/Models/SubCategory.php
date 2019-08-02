<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model implements \Countable {

	protected $table = 'subcategories';
	protected $fillable = ['subcategory', 'category_id', 'slug'];

	function Category()
	{
		return $this->belongsTo('App\Category');
	}

	function Articles()
	{
		return $this->hasMany('App\Article', 'subcategory_id');
	}

	function scopeWithName($query, $name)
	{
		return $query->whereNameEn($name);
	}

    public function articleTags(){
        return $this->hasMany('App\ArticleTags', 'subcategory_id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
