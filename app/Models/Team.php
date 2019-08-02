<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model implements \Countable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "teams";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'type', 'name', 'designation', 'photo', 'joined', 'about_me', 'favourite_section', 'women_inspire', 'motherly_advice', 'power_for_one_day', 'one_line', 'favorite_book', 'pass_time', 'email', 'fb', 'gp', 'linked', 'is_present'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
