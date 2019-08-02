<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertEarningReportLog extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'earning_report_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['data', 'is_send'];
    
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
