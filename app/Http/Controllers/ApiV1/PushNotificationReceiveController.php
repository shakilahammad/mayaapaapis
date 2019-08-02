<?php

namespace App\Http\Controllers\ApiV1;

use App\Models\PushNotificationMessage;
use App\Models\PushNotificationReceive;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PushNotificationReceiveController extends Controller
{
    //

    public function postReceivedPushNotification(Request $request){

        return response()->json([
            'status' => 'success',
            'data' => null,
            'error_code' => 0,
            'error_message' => '',
        ]);

//        dd($request->user_id);
//        try{
//            $data = PushNotificationReceive::create([
//                "user_id" => $request->user_id,
//                "title" => $request->titles,
//                "body" => $request->bodies,
//                "noti_type" => $request->noti_type,
//                "action_type" => $request->action_type,
//                "class_type" => $request->class_type,
//                "class_name" => $request->class_name,
//                "promo_code" => $request->promo_code,
//                "url" => $request->url,
//                "image_url" => $request->image_url,
//                "header_text" => $request->header_text,
//                "details_text" => $request->details_text,
//                "btn_text" => $request->btn_text,
//                "log_in_needed" => $request->log_in_needed,
//                "question_id" => $request->question_id,
//                "noti_task" => $request->noti_task,
//                "action_data" => $request->action_data
//            ])->toArray();
//
////            dd($data);
//
////            $data = array_map(function ($key, $value){
////                dd($key, $value);
////                return array(
////                    "titles" => $value["title"],
////                    "bodies" => $value["body"]
////                );
////            }, $data);
//
//
//            // changing title -> titles, body->bodies
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
//
////            dd($data1);
//            return $this->sendSuccessResponse($data1);
//
//        }catch (\Exception $exception){
//            return $this->sendFailureResponse(null);
//        }

    }

    public function callPushAction() {
        $url = 'http://52.76.173.213/search_views_push/';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => $url
        ]);
        $results = curl_exec($curl);
        curl_close ($curl);

        return $results;
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

    public function sendFailureResponse($data)
    {
        return response()->json([
            'status' => 'failure',
            'data' => $data,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }
}
