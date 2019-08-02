<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model implements \Countable
{
//    protected $fillable = ['drugs', 'investigations'];

    protected $guarded = ['id'];

//    protected $hidden =['id'];

    protected $casts = [
        'drugs' => 'array',
        'investigations' => 'array'
    ];

    function specialist()
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
