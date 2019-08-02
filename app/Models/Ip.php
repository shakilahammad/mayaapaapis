<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ip extends Model implements \Countable
{
    protected $table = 'ips';
    protected $fillable = ['id', 'ip', 'forwarded', 'created_at', 'updated_at'];

    function Logs()
    {
        return $this->hasMany('Log', 'ip', 'ip');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
