<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIResponseLog extends Model implements \Countable
{
    protected $table = 'ai_response_logs';

    protected $guarded = ['id'];

    protected $casts = [
      'suggested_answer' => 'array'
    ];
    
    private $count = 0;

    /**
     * Get unserialize Data
     *
     * @param $value
     * @return string
     */
//    public function getSuggestedAnswerAttribute($value)
//    {
//        return unserialize($value);
//    }

    /**
     * Store data after serialize
     *
     * @param $value
     */
//    public function setSuggestedAnswerAttribute($value)
//    {
//        $this->attributes['suggested_answer'] = serialize($value);
//    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
