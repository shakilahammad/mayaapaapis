<?php
/**
 * Created by PhpStorm.
 * User: razib
 * Date: 2019-07-07
 * Time: 10:32
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PocoSession extends Model implements \Countable
{
    protected $primaryKey = 'session';
    protected $table = 'poco_sessions';
    protected $fillable = ['session'];
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}