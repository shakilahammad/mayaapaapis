<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerAuth extends Model implements \Countable
{
    protected $table = 'partner_auths';

    protected $fillable = ['id', 'user_id', 'password'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
