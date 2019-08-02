<?php

namespace App\Listeners;

use App\Classes\Miscellaneous;
use App\Classes\NotificationForUser;
use App\Events\CreatePointTransaction;
use App\point_transactions;
use App\user_points;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointTransactionCreated implements ShouldQueue
{
//    use InteractsWithQueue;

    public $connection = 'database';
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CreatePointTransaction  $event
     * @return void
     */
    public function handle(CreatePointTransaction $event)
    {
        $transaction = $this->create_transaction($event->user_id, $event->source_id);

        Log::emergency("loc CreatePointTransaction event.". json_encode($event));
        if($transaction['status'] === 'success'){

//            if($event->user_id === 301844)
//                Log::emergency('Razib CreatePointTransaction event');

            $notification = new \stdClass();
//
            $Message = (object) $transaction;
//
            $notification->notifiable = $event->user_id;
            $notification->Message = $Message;

            # send notification

//            $notification = new \stdClass();

//            $obj = (object) ['status' => "success", "source_title"=> 'Good morning',
//                "source_sub_title"=>'sdfsd',"source_type"=>'sdfsdf',
//                "action_type" => 'like' , "earned_point_for_the_action"=>55,
//                "total_point"=>666, "current_batch"=>1 ,
//                "is_badge_just_upgraded"=>false, "next_upper_badge"=>0
//            ];
//
//            $notification->notifiable = 301844;
//            $notification->Message = $obj;

            NotificationForUser::checkRecipientsForMayaPoints($notification);

        }
    }

    public function failed(CreatePointTransaction $event, $exception){
        Log::emergency($exception->getMessage() . ' '. json_encode($exception));
    }

    public function create_transaction($user_id, $source_id){

//        $user_id = $user_id;
//        $source_id = $source_id;

        $get_source_detail =DB::table("point_sources")
            ->where('id','=', $source_id)
            ->get();

        $title_en = $get_source_detail[0]->title_en;
        $title_bn = $get_source_detail[0]->title_bn;
        $message_en = $get_source_detail[0]->message_en;
        $message_bn = $get_source_detail[0]->message_bn;
        $sub_title_en = $get_source_detail[0]->sub_title_en;
        $sub_title_bn = $get_source_detail[0]->sub_title_bn;
        $type = $get_source_detail[0]->type;
        $point = $get_source_detail[0]->point;
        $action_type = $get_source_detail[0]->action_type;

        $user_data = user_points::where('user_id', $user_id)->first();  //getting user data

        $total_points =$point;
        if ($user_data !=null){
            $total_points=$user_data->total_points + $total_points;
            $user_data->total_points = $total_points;
            $user_data->save();
            #send_push()

        }else{
            $user_points = new user_points;
            $user_points->user_id= $user_id;
            $user_points->total_points = $total_points;
            $user_points->save();
            #send_push()
        }

        $point_transactions = new point_transactions();
        $point_transactions->user_id = $user_id;
        $point_transactions->source_id = $source_id;
        $point_transactions->save();

        $user_badge = DB::table("point_user_badges")
            ->where("user_id", $user_id)
            ->get();

        $next_upper_badge =0;
        $current_badge_id = 5;


        if($user_badge->count() == 0) {

            $current_badge_id = 5;
            $next_upper_badge = $current_badge_id - 1;

        }else{
            $current_badge_id=0;
            $current_badge_id = $user_badge[0]->badge_id;

            if ($current_badge_id == 1){

                return ['status' => "success", "source_title_en"=> $title_en,
                    "source_title_bn"=> $title_bn,
                    "source_sub_title_en"=>$sub_title_en,
                    "source_sub_title_bn"=>$sub_title_bn,
                    "message_en" =>$message_en,
                    "message_bn" =>$message_bn,
                    "source_type"=>$type,
                    "action_type" => $action_type , "earned_point_for_the_action"=>$point,
                    "total_point"=>$total_points, "current_batch"=>$current_badge_id ,
                    "is_badge_just_upgraded"=>false, "next_upper_badge"=>0
                ];

//                return response()
//                    ->json(['status' => "success", "source_title"=> $title,
//                        "source_sub_title"=>$sub_title,"source_type"=>$type,
//                        "action_type" => $action_type , "earned_point_for_the_action"=>$point,
//                        "total_point"=>$total_points, "current_batch"=>$current_badge_id ,
//                        "is_badge_just_upgraded"=>false, "next_upper_badge"=>0
//                    ]);

                #no upper badge
            }
            else {
                $next_upper_badge = $current_badge_id - 1;
            }
        }


        $badge_criteria=Miscellaneous::chacking_badge_criteria($user_id, $next_upper_badge);
        $point_criteria = Miscellaneous::checking_badge_point($user_id,$next_upper_badge);
        $is_badge_just_upgraded = false;

        if ($badge_criteria  ==1 & $point_criteria ==1){
            $is_badge_just_upgraded = true;
            $current_time = date("Y:m:d h:i:s");
            DB::table('point_user_badges')
                ->updateOrInsert(
                    ['user_id' => $user_id],
                    ['badge_id' => $next_upper_badge, 'created_at'=>$current_time, 'updated_at'=> $current_time]
                );
            $current_badge_id = $next_upper_badge;

            #send push that he just upgade to new label
            #pending for push notifications
        }

        return ['status' => "success", "source_title_en"=> $title_en,
            "source_title_bn"=> $title_bn,
            "source_sub_title_en"=>$sub_title_en,
            "source_sub_title_bn"=>$sub_title_bn,
            "message_en" =>$message_en,
            "message_bn" =>$message_bn,
            "source_type"=>$type,
            "action_type" => $action_type , "earned_point_for_the_action"=>$point,
            "total_point"=>$total_points, "current_batch"=>$current_badge_id ,
            "is_badge_just_upgraded"=>$is_badge_just_upgraded
        ];

//        return response()
//            ->json(['status' => "success", "source_title"=> $title,
//                "source_sub_title"=>$sub_title,"source_type"=>$type,
//                "action_type" => $action_type , "earned_point_for_the_action"=>$point,
//                "total_point"=>$total_points, "current_batch"=>$current_badge_id ,
//                "is_badge_just_upgraded"=>$is_badge_just_upgraded
//            ]);
    }
}
