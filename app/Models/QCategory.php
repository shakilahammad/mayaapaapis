<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QCategory extends Model implements \Countable {

	protected $table = 'qcategories';
	protected $fillable = ['category', 'description'];

	function Questions()
	{
		return $this->hasMany('App\Question', 'qcategory_id');
	}

	function scopeFindByCategory($query, $category)
	{
		return $query->whereCategory($category);
	}

	function Faqs()
	{
		return $this->hasMany('App\Faq', 'qcategory_id');
	}

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
