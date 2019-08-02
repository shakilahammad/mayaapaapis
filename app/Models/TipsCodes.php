<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipsCodes extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "tips_codes";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'code', 'product_id', 'service_id'
    ];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
