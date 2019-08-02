<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model implements \Countable
{
    protected $table = 'invites';

    protected $fillable = ['id', 'code_id', 'recipient_id','exp_date','session', 'updated_at'];

    public function codes()
    {
        return $this->belongsTo(InviteCode::class, 'code_id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
