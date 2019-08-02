<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model implements \Countable {

	protected $table = 'categories';
	protected $fillable = ['category'];
	private $count = 0;

	function SubCategories()
	{
		return $this->hasMany('App\SubCategory');
	}

	function Articles()
	{
		return $this->hasManyThrough('App\Article', 'App\SubCategory', 'category_id', 'subcategory_id');
	}

	function scopeWithName($query, $name)
	{
		return $query->whereNameEn($name);
	}

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
