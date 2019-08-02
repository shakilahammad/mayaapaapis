<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecondaryProfile extends Model implements \Countable
{
    protected $table = 'secondary_profiles';

    protected $fillable =
	[
         'email',
         'relation',
         'age',
         'gender',
         'specialist_id',
         'note'
	];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
