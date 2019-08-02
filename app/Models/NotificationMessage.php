<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationMessage extends Model implements \Countable
{
    /**
     * The database table used by the model
     *
     * @var string
     */
    protected $table = 'notifications_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'type', 'title', 'title_bn', 'details', 'details_bn' ];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
