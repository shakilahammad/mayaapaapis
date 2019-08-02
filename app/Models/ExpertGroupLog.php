<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertGroupLog extends Model implements \Countable
{
     /**
     * The database table used by the model.
     *
     * @var string
     */
     protected $table = 'expert_groups_logs';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     protected $fillable = ['assigned_to', 'assigned_by', 'assigned_group_id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
