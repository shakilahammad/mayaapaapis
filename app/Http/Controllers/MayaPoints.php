<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Point_Source;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;

class MayaPoints extends Controller
{
    //

    public function earning_source(Request $request){
        try {
            $current_time = date("Y:m:d h:i:s");

            $input = DB::table('point_sources')
                ->where("source_expiry_time",">=",$current_time)
                ->get();


            return response()->json(['status' => "success", "time"=> $current_time,"data" => $input,]);
        }catch (Exception $e){
            return response()->json(['status' => "fail", "data" => []]);
        }
    }

    public function create_transaction(Request $request){
        dd("tareq");
        return response()->json(['status' => "fail", "data" => "tareq"]);
    }



}
