<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class NonPremiumNotification extends Model implements \Countable
{

    protected $table = 'non_premium_notifications';

    protected $fillable = ['id', 'question_id', 'notifiable', 'notifier_id', 'notifications_message_id', 'send_at'];

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
     * Relationship with questions table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
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
