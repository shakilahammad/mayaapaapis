<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertGroup extends Model implements \Countable
{
     /**
     * The database table used by the model.
     *
     * @var string
     */
     protected $table = 'expert_groups';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     protected $fillable = ['expert_id', 'group_id'];

     public function group()
     {
        return $this->hasOne(Group::class, 'id');
     }
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
