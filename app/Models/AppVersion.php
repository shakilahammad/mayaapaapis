<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "app_version";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['package_name', 'version_code','must_update'];
    
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
