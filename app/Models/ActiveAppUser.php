<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveAppUser extends Model implements  \Countable
{
    protected $table = 'active_app_users';

    protected $guarded = ['id'];

    private  $count = 0;

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
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
