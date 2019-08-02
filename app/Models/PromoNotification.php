<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoNotification extends Model implements \Countable
{

    protected $table = 'notifications_promo';

    protected $guarded = ['id'];

    /**
     * Relationship for notifiable
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function notifiable()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with notifications_messages table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Message()
    {
        return $this->belongsTo(NotificationMessage::class, 'notifications_message_id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
