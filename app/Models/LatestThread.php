<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LatestThread extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'latest_thread';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['thread'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
