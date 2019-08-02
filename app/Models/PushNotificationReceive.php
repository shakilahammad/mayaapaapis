<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotificationReceive extends Model implements \Countable
{
    //

    protected $fillable = [
        "user_id",
        "title",
        "body",
        "noti_type",
        "action_type",
        "class_type",
        "class_name",
        "promo_code" ,
        "url",
        "image_url" ,
        "header_text",
        "details_text",
        "btn_text",
        "log_in_needed" ,
        "question_id" ,
        "noti_task" ,
        "action_data"
    ];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
