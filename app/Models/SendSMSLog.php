<?php

namespace App\Models;

use App\Question;
use Illuminate\Database\Eloquent\Model;

class SendSMSLog extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'send_sms_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'question_id', 'specialist_id', 'type'];

    /**
     * Relationship with user's table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function questions()
    {
        return $this->belongsTo(Question::class, 'id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
