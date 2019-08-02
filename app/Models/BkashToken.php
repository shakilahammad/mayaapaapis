<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BkashToken extends Model implements \Countable
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bkash_tokens';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
