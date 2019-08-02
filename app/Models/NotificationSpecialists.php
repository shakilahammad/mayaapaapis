<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class NotificationSpecialists extends Model implements \Countable
{
    /**
     * The database table used by the model
     *
     * @var string
     */
    protected $table = 'notifications_specialists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['question_id', 'notifiable', 'notifier_id', 'notification_message_id', 'seen'];

    /**
     * Get created at in readable format
     *
     * @param $value
     * @return string
     */
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d M Y  D g:i A');
    }

    /**
     * Get updated at in readable format
     *
     * @param $value
     * @return string
     */
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d M Y  D g:i A');
    }

    /**
     * Relationship with question table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Relationship with user table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Specialist()
    {
        return $this->belongsTo(User::class, 'id');
    }

    /**
     * Relationship with notification messages table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Message()
    {
        return $this->belongsTo(NotificationMessage::class, 'notification_message_id', 'id');
    }

    /**
     * Get Notifiable info
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Notifiable()
    {
        return $this->belongsTo(User::class, 'notifiable', 'id');
    }

    /**
     * Get notifier info
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Notifier()
    {
        return $this->belongsTo(User::class, 'notifier_id', 'id');
    }

    /**
     * Get notifications by specialistId, type and status
     *
     * @param $query
     * @param $notifiable
     * @param $status
     * @return mixed
     */
    public function scopeGetNotifications($query, $notifiable, $status)
    {
        return $query->whereNotifiable($notifiable)->whereSeen($status)->with('message')->orderBy('id', 'desc');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
