<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumUser extends Model implements \Countable
{
    protected $table = 'premium_users';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
