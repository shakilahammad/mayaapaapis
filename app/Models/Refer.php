<?php

namespace App\Models;

use App\Http\Helper;
use Illuminate\Database\Eloquent\Model;

class Refer extends Model implements \Countable
{
    protected $table = "refers";

    protected $fillable = ['referred_to', 'referred_by', 'question_id'];

    //    protected $dispatchesEvents = [
//        'saved' => ReferSaved::class
//    ];

    public function scopeDisplayableQuestions($query, $expert_id)
    {
        $expert = User::with(['specialistProfile' => function($query){
            $query->where('team', 'Psychosocial');
        }])->find($expert_id);

        if (count($expert)) {
            $pychoExpert = User::select('id')->where('email', Helper::maya_encrypt('psycho@maya.com.bd'))->first();
            return $this->getReferWithQuestion($query, $expert_id, $pychoExpert);
        }

        return $this->getReferWithQuestion($query, $expert_id, 0);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function referred_to()
    {
        return $this->belongsTo(Question::class, 'referred_to');
    }

    public function referred_by()
    {
        return $this->belongsTo(Question::class, 'referred_by');
    }

    public function getReferWithQuestion($query, $expert_id, $pychoExpert)
    {
        if (empty($pychoExpert)){
            return $query->with(['question' => function($query){
                $query->where('is_premium', 1)->where('status', 'pending');
            }])->where('referred_to', $expert_id);
        }

        return $query->with(['question' => function ($query) {
            $query->where('is_premium', 1)->where('status', 'pending');
        }])->whereIn('referred_to', [$expert_id, $pychoExpert->id]);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
