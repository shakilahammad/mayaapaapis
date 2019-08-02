<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftAnswer extends Model implements \Countable
{
    /**
     * @var array
     */
    protected $fillable = ['question_id', 'specialist_id', 'body'];

    private $count = 0;

    /**
     * Relationship with users tbale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialist()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with questions tbale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
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
