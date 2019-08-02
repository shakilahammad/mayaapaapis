<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'question_id', 'notifiable', 'notifier_id', 'notifications_message_id', 'is_seen', 'count', 'last_update'];

    protected $appends = ['notification_message_title'];
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

    public function getNotificationMessageTitleAttribute()
    {
        return $this->Message->title;
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
