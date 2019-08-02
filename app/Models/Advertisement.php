<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertisement extends Model implements \Countable
{
    use SoftDeletes;

    protected $table = 'advertisements';

    protected $fillable = [
        'url',
        'priority',
        'version',
        'destination',
        'party',
        'type',
        'page'
    ];

    protected $hidden = ['deleted_at'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }


}
