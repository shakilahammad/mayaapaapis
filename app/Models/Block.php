<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_block';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'is_permanent'];

    private $count = 0;

    /**
     * Relationship with user's table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
