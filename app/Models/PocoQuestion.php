<?php
/**
 * Created by PhpStorm.
 * User: razib
 * Date: 2019-07-07
 * Time: 10:34
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PocoQuestion extends Model implements \Countable
{
    protected $table = 'poco_questions';
//    protected $fillable = ['phone', 'operator'];
    protected $guarded = ['id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}