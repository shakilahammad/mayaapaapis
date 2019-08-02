<?php

namespace App\Http\Controllers\APIs\V2;

use App\Models\PushNotificationMessage;
use App\Models\PushNotificationReceive;
use App\Models\PushNotificationUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PushNotificationReceiveController extends Controller
{

    public function postReceivedPushNotification(Request $request){
//        return $this->sendSuccessResponse('');
        try{
            $pn = new PushNotificationMessage();
            $data = $pn->save_user($request);

//            $pu = new PushNotificationUsers();
//            $

//            dd($data);

//            $data = array_map(function ($key, $value){
//                dd($key, $value);
//                return array(
//                    "titles" => $value["title"],
//                    "bodies" => $value["body"]
//                );
//            }, $data);


            // changing title -> titles, body->bodies
//            $data1["user_id"] = $data["user_id"];
//            $data1["titles"] = $data["title"];
//            $data1["bodies"] = $data["body"];
//            $data1["noti_type"] = $data["noti_type"];
//            $data1["action_type"] = $data["action_type"];
//            $data1["class_type"] = $data["class_type"];
//            $data1["class_name"] = $data["class_name"];
//            $data1["promo_code"] = $data["promo_code"];
//            $data1["url"] = $data["url"];
//            $data1["image_url"] = $data["image_url"];
//            $data1["header_text"] = $data["header_text"];
//            $data1["details_text"] = $data["details_text"];
//            $data1["btn_text"] = $data["btn_text"];
//            $data1["log_in_needed"] = $data["log_in_needed"];
//            $data1["question_id"] = $data["question_id"];
//            $data1["noti_task"] = $data["noti_task"];
//            $data1["action_data"] = $data["action_data"];
//            $data1["pnm_id"] = $data["pnm_id"];

//            dd($data1);
            return $this->sendSuccessResponse($data);

        }catch (\Exception $exception){
            return $this->sendFailureResponse($exception);
        }

    }

    public function sendSuccessResponse($data)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function sendFailureResponse($exception)
    {
        return response()->json([
            'status' => 'failure',
            'data' => null,
            'error_code' => 0,
            'error_message' => $exception->getMessage(),
        ]);
    }
}
