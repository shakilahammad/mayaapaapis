<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileOperatorsLogs extends Model implements \Countable
{
    protected $table = 'mobile_operators_logs';

    protected $fillable = ['id', 'mobile_operators_id', 'xml_data'];

    public function mobile_operators(){
        return $this->belongsTo('App\Models\MobileOperators');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
