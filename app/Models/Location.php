<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model implements \Countable
{
    protected $table = 'locations';

    protected $fillable = ['user_id', 'lat', 'long', 'area', 'city', 'country', 'location'];

    private $count = 0;

    public function question()
    {
        return $this->hasOne(Question::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
