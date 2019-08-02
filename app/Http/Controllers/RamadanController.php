<?php
/**
 * Created by PhpStorm.
 * User: razib
 * Date: 2019-05-02
 * Time: 14:29
 */

namespace App\Http\Controllers;

use App\Models\AccessToken;
use App\Models\DailyRamadan;
use App\Models\RamadanCheckin;
use App\Models\TrackDownload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RamadanController extends Controller
{
    public function __construct()
    {

    }

    public function dailyRamadan(Request $request){
//        dd("sdf");

        $data = $request->all();
//        dd($data);

        $daily_ramadan = DailyRamadan::whereDate('iftar_time', Carbon::today())->first();

        $datas = [
            'data' => $data,
            'daily_ramadan' => $daily_ramadan
        ];


        return view('daily_ramadan')->with($datas);
    }

    public function dailyRamadanCheckIn(Request $request, $user_id){

        $ramadan_checkin = RamadanCheckin::where('user_id', $user_id)
            ->orderBy('created_at', 'asc')
            ->get();

        $tip = DailyRamadan::whereDate('iftar_time', Carbon::today())->first();

//        dd($ramadan_checkin);
        $ramadan_checkin_data = $this->getFormattedCheckins($ramadan_checkin);

        $data = [
            'check_ins' => $ramadan_checkin_data,
            'total_checkin' => count($ramadan_checkin),
            'tips' => $tip->tips
        ];


        return view('ramadan_checkin')->with($data);
    }

    public function RamadanCheckInAuth(Request $request){

//        dd($request->header('user_id'));
//        dd($request->header('access-token'));

//        $access_token = AccessToken::whereToken($request->header('access-token'))->first();
//        dd(count($access_token));
        $data = [
            "status" => "success"
        ];

        $request_data = $request->all();

        try{
            $ramadan_checkin = RamadanCheckin::where('user_id', $request_data['user_id'])
                ->whereDate('created_at', Carbon::today())->first();


            if(count($ramadan_checkin)){
                return response()->json($data);
            }

//            dd($device_id);
//            dd($request->header('user_id'), $device_id, $request->header('access-token'));

            $track_download = TrackDownload::where('device_id', $request_data['device_id'])->first();

            if(!count($track_download)){
                $track_download = TrackDownload::create([
                    'device_id' => $request_data['device_id']
                ]);
            }


            $ramadan_checkin = RamadanCheckin::create([
                'user_id' => $request_data['user_id'],
                'checked_in' => true,
                'track_download_id' => $track_download->id
            ]);

            return response()->json($data);

        } catch (\Exception $exception)
        {
            $data = [
                'status' => 'failure',
                'message' => $exception->getMessage()
            ];

            return response()->json($data);
//            Log::emergency($exception->getMessage() . $exception->getFile() . $exception->getLine());
        }

    }

    public function getFormattedCheckins($result){
        $data = [];

        $data_rohmot = [
            "7" => false,
            "8" => false,
            "9" => false,
            "10" => false,
            "11" => false,
            "12" => false,
            "13" => false,
            "14" => false,
            "15" => false,
            "16" => false
        ];

        $data_maghfirat = [
            "17" => false,
            "18" => false,
            "19" => false,
            "20" => false,
            "21" => false,
            "22" => false,
            "23" => false,
            "24" => false,
            "25" => false,
            "26" => false
        ];

        $data_nazat = [
            "27" => false,
            "28" => false,
            "29" => false,
            "30" => false,
            "31" => false,
            "1" => false,
            "2" => false,
            "3" => false,
            "4" => false,
            "5" => false
        ];

//        dd($data_maghfirat[7]);

        foreach ($result as $check_in){
           $day = \Carbon\Carbon::parse($check_in->created_at)->day;
            $month = \Carbon\Carbon::parse($check_in->created_at)->month;

           if( $day <= 16 && $month == 5){
               $data_rohmot[$day] = true;
           }
           else if( $day <= 26 && $month == 5){
                $data_maghfirat[$day] = true;
            }
           else{
                $data_nazat[$day] = true;
           }
        }

        array_push($data, $data_rohmot);
        array_push($data, $data_maghfirat);
        array_push($data, $data_nazat);

        return $data;

//        for($i = 1; i<=31; $i++){
//            \Carbon\Carbon::parse($check_ins[0]->created_at)->day
//        }
    }
}