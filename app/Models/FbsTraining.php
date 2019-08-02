<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FbsTraining extends Model implements \Countable
{
    protected $table = 'fbs_trainings';

    protected $fillable = ['name', 'date'];

    public function fbsUsers()
    {
        return $this->hasMany(FbsUser::class);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
