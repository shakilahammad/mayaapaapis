<?php

namespace App\Models;;

use Illuminate\Database\Eloquent\Model;

class TrackDownload extends Model implements \Countable
{
    protected $table = 'track_download';

    protected $fillable = ['id', 'device_id', 'source'];

    private $count = 0;

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
