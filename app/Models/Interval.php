<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Interval extends Model implements \Countable {
    protected $table = 'intervals';
    protected $fillable = ['start', 'end', 'duration', 'day'];
    public $timestamp = false;

    function scopeSpecialistsOnDay($query, $interval_id, $day)
    {
        $query = DB::select(DB::raw("select * from users u join intervals_specialists rel where rel.interval_id = $interval_id and rel.day = '$day' and rel.specialist_id = u.id"));

        return User::modelsFromRawResults($query);
    }

    function scopeWithSpecialistOnDay($query, $id, $day)
    {
        switch ($day) {
            case 0:
                $day = 'Sunday';
                break;
            case 1:
                $day = 'Monday';
                break;
            case 2:
                $day = 'Tuesday';
                break;
            case 3:
                $day = 'Wednesday';
                break;
            case 4:
                $day = 'Thursday';
                break;
            case 5:
                $day = 'Friday';
                break;
            default:
                $day = 'Saturday';
                break;
        }
        $rows = DB::table('intervals_specialists')->whereSpecialistId($id)->where('day', '=', $day)->get();
        $intervals = array();
        foreach ($rows as $key => $value) {
            array_push($intervals, Interval::find($value->interval_id));
        }
        return $intervals;
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
