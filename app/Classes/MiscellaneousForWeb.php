<?php

namespace App\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MiscellaneousForWeb
{
    public static function fetchMostlyUsedTag()
    {
        $lastSevenDays = Carbon::now()->subDays(30)->toDateTimeString();
        return DB::select("SELECT t.id, count(*) as count, t.name_en, t.name_bn FROM questions_tags qt, tags t WHERE qt.tag_id = t.id and qt.created_at > '{$lastSevenDays}' GROUP BY t.id ORDER BY count DESC LIMIT 0, 5");
    }

}
