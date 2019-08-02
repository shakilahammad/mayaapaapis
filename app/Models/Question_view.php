<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Events\Literacy\SendLiteracyNotification;

class Question_view extends Model implements \Countable
{
    protected $table = 'question_views';

    protected $guarded = ['id'];

    /**
     * The event map for the model.
     *
     * @var array
     */
//    protected $dispatchesEvents = [
//        'created' => SendLiteracyNotification::class
//    ];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
