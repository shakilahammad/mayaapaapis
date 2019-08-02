<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medium extends Model implements \Countable
{
    protected $table = 'media';

    protected $fillable = ['id', 'type', 'source', 'endpoint'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}




