<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model implements \Countable
{
    protected $guarded = ['id'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function givePermissionTo(Permission $permission)
    {
        return $this->permissions()->save($permission);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
