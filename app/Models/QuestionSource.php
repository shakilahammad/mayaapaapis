<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionSource extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "questions_source";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'question_id', 'device_name', 'platform', 'platform_version', 'browser', 'browser_version', 'is_desktop', 'is_tablet', 'is_mobile', 'is_phone', 'is_robot' ];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
