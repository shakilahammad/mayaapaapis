<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocioEconomicUser extends Model implements \Countable
{
    protected $table = 'socio_economic_users';

    protected $guarded = ['id'];

//    public function __construct($user_id, $attributes = array()) {
//
//        parent::__construct($attributes); // Calls Default Constructor
//
//        $this->user_id = $user_id;
//    }

//    public static function all($columns = array('*'))
//    {
//        $columns = is_array($columns) ? $columns : func_get_args();
//
//        $instance = new static;
//
//        return $instance->newQuery()->where('id', '<>', '1')->get($columns);
//    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
