<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Group extends Model implements \Countable
{
     /**
     * The database table used by the model.
     *
     * @var string
     */
     protected $table = 'groups';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'expert_groups', 'group_id', 'expert_id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
