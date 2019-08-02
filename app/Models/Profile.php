<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model implements \Countable {

    use SoftDeletes;

    protected $table = 'profiles';

    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'age',
        'city',
        'country',
        'location',
        'gender',
        'marital_status',
        'organisation',
        'designation'
    ];
    protected $dates = ['deleted_at'];

    public function Questions()
    {
        return $this->hasMany('Question', 'email');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}




