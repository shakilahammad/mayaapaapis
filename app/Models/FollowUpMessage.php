<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUpMessage extends Model implements \Countable
{
    protected $table = 'followup_messages';

    protected $fillable = ['followup_id', 'message_body'];

    public function followup()
    {
        return $this->belongsTo(FollowUp::class);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
