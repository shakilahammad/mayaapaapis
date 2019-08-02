<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model implements \Countable
{

    protected $guarded = ['id'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
