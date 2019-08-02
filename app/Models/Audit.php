<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model implements \Countable
{
    protected $guarded = ['id'];
    private $count = 0;

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function auditedBy()
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
