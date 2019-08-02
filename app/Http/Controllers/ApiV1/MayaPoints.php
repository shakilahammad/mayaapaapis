<?php

namespace App\Http\Controllers\ApiV1;

use App\Classes\Miscellaneous;
use App\Events\CreatePointTransaction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Payment\Bkash\BkashController;
use App\Models\PremiumPackage;
use App\Models\PremiumPayment;
use App\Models\PremiumUser;
use App\Models\Question;
use App\Models\User;
use App\Point_Badge;
use App\Point_Badge_Criterion;
use App\Point_Source;
use App\point_transactions;
use App\User_Badge;
use App\user_points;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use test\Mockery\ReturnTypeObjectTypeHint;


class MayaPoints extends Controller
{
    public function earning_source(Request $request){
        try {
            $current_time = date("Y:m:d h:i:s");

            $input = DB::table('point_sources')
                ->where("source_expiry_time",">=",$current_time)
                ->get();


            return response()->json(['status' => "success", "data" => $input,]);
        }catch (\Exception $e){
            return response()->json(['status' => "fail", "data" => []]);
        }
    }

    public function create_transaction(Request $request){

        $user_id = $request->user_id;
        $source_id = $request->source_id;

        $get_source_detail =DB::table("point_sources")
        ->where('id','=', $source_id)
        ->get();

        $title_en = $get_source_detail[0]->title_en;
        $title_bn = $get_source_detail[0]->title_bn;
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

        $point_transactions = new point_transactions;
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
            $current_badge_id = $user_badge[0]->badge_id;

            if ($current_badge_id == 1){
                return response()
                    ->json(['status' => "success", "source_title_en"=> $title_en,  "source_title_bn"=> $title_bn,
                        "source_sub_title_en"=>$sub_title_en,"source_sub_title_bn"=>$sub_title_bn, "source_type"=>$type,
                        "action_type" => $action_type , "earned_point_for_the_action"=>$point,
                        "total_point"=>$total_points, "current_batch"=>$current_badge_id ,
                        "is_badge_just_upgraded"=>false, "next_upper_badge"=>0
                    ]);

                #no upper badge
            }
            else {
                #batch_goes down

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

        return response()
            ->json(['status' => "success", "source_title_en"=> $title_en,  "source_title_bn"=> $title_bn,
                "source_sub_title_en"=>$sub_title_en,"source_sub_title_bn"=>$sub_title_bn, "source_type"=>$type,
                "action_type" => $action_type , "earned_point_for_the_action"=>$point,
                "total_point"=>$total_points, "current_batch"=>$current_badge_id ,
                "is_badge_just_upgraded"=>false,"next_upper_badge"=>$next_upper_badge,
                ]);
    }


    public  function  get_all_transaction_history(Request $request, $userId){
        $total_points_data = DB::table("user_points")->where("user_id", "=", $userId)->first();

        $total_points=$total_points_data->total_points;

        $users_all_transactions = DB::table('point_transactions')
            ->join('point_sources', 'point_transactions.source_id', '=', 'point_sources.id')
            ->select('point_transactions.id as transactions_id','point_sources.id as source_id', "point_sources.type as transactions_type ",'point_sources.title_en', 'point_sources.title_bn', 'point_sources.message_en as sub_title_en','point_sources.message_bn as sub_title_bn','point_sources.point', 'point_sources.action_type', 'point_sources.source_expiry_time')
            ->where("user_id", '=' , $userId)
            ->orderBy('point_transactions.id', 'desc')
            ->limit(20)
            ->get();
        if ($users_all_transactions->count()==0){
            return response()->json(['status'=>"failure","tatal_points"=>0, "data"=>$users_all_transactions ]);
        }
//        dump($users_all_transactions);
        return response()->json(['status'=>"success", "tatal_points"=>$total_points,"data"=>$users_all_transactions ]);

    }



    private function  checking_point_requirements($total_point, $next_badge_id){

       $next_badge_info= DB::table("point_badges")->where("id",'=',$next_badge_id)->get();

       $required_to_achive_next_badge = $next_badge_info[0]->required_points;


       if($total_point> $required_to_achive_next_badge){
            return 0;
       }
       else{
           return $required_to_achive_next_badge - $total_point;
       }

    }




    private function checking_badge_requirements($user_id, $next_batch_id){

        $criterion_for_badge = DB::table("point_badge_criterion")
            ->join("point_sources", "point_badge_criterion.source_id",'=', 'point_sources.id')
            ->where("badge_id","=",$next_batch_id)->get();

        $length = $criterion_for_badge->count();
        $criterion_data = array();
        $user_data = array();

        $source_title_array = array();

        foreach ($criterion_for_badge as $value){
            $source_id= $value->source_id ;
            $source_title_en = $value->title_en;
            $num_of_transaction=$value->num_of_transaction;
            $criterion_data[$source_id]=$num_of_transaction;
            $user_data[$source_id] = 0;
            $source_title_array[$source_id] = $source_title_en;

        }

        $user_transaction_entry = DB::table("point_transactions")
            ->select(DB::raw('count(*) as user_count, source_id'))
            ->where("user_id","=",$user_id)
            ->groupBy("source_id")
            ->get();

        foreach ($user_transaction_entry as $value){
            $source_id = $value->source_id;
            $num_of_transaction = $value->user_count;
            $user_data[$source_id] = $num_of_transaction;
        }

        $return_data = array();

        foreach ($criterion_data as $key => $value) {
            $user_transactions = $user_data[$key];
            if($user_transactions < $value){
                $source_title_en =$source_title_array[$key];
                $return_data[$source_title_en] = $value - $user_transactions;

            }
            else{
                $source_title_en =$source_title_array[$key];
                $return_data[$source_title_en] = 0;
            }

        }
        return $return_data;

    }



    public function get_user_total_point_and_badge(Request $request,$userId ){
        $user_badge = DB::table("point_user_badges")
            ->where("user_id", $userId)
            ->get();

        $user_pont_data=DB::table("user_points")->where("user_id",'=', $userId)->get();
        $total_point = 0;
        if($user_pont_data->count()==0){
            $total_point =0;
        }
        else{
            $total_point=$user_pont_data[0]->total_points;
        }
        $next_upper_badge_name="";
        $next_upper_badge =5;
        $current_badge_id = 5;

        if($user_badge->count() == 0) {

            $current_badge_id = 5;
            $next_upper_badge = $current_badge_id - 1;

        }
        else{
            $current_badge_id = $user_badge[0]->badge_id;

            if ($current_badge_id == 1){
                $current_badge_id = 1;
                $empty_array=array();
                return response()->json(['status'=>"success", "current_badge_name"=> "Platinum",'current_badge_id'=>$current_badge_id, "total_points"=>$total_point, "required_point_for_badge_update"=>$empty_array, "need_transactions"=>$empty_array, "next_upper_badge_name"=>$next_upper_badge_name]);
            }
            else {
                $next_upper_badge = $current_badge_id - 1;
            }
        }
        $badge_info = DB::table("point_badges")
            ->where("id", $current_badge_id)
            ->first();
        $current_badge_name = $badge_info->name;

        $badge_info = DB::table("point_badges")
            ->where("id", $next_upper_badge)
            ->first();
        $next_upper_badge_name = $badge_info->name;


        $required_point_for_update = $this->checking_point_requirements($total_point, $next_upper_badge);
        $required_transaction_for_update = $this->checking_badge_requirements($userId,$next_upper_badge);

        return response()->json(['status'=>"success", "current_badge_name"=> $current_badge_name,'current_badge_id'=>$current_badge_id, "total_points"=>$total_point, "required_point_for_badge_update"=>$required_point_for_update, "need_transactions"=>$required_transaction_for_update,  "next_upper_badge_name"=>$next_upper_badge_name]);

    }


    private function createPremiumUser($user, $invoiceId)
    {

        try {
            $info = [
                'user_id' => $user->id,
                'email' => $user->email,
                'name'=> $user->f_name . $user->l_name,
                'phone' => $user->phone ?? '',
                'invoice_id' => $invoiceId,
                'city' => optional($user->location)->city ?? '',
                'address' => optional($user->location)->location ?? '',
                'country' => optional($user->location)->country ?? ''
            ];

            PremiumUser::updateOrCreate(['user_id' => $user->id], $info);
            return response()->json([
                'status' => 'success'
            ]);
        }catch (\Exception $exception){

            return response()->json([
                'status' => 'failure'
            ]);
        }

    }

    private function createPremiumPayment($user, $package, $invoiceId)
    {
        try {
        $data = [
            'currency' => 'points',
            'provider' => 'maya_points',
            'package_id' => $package->id,
            'effective_time' => Carbon::now(),
            'expiry_time' => Carbon::now()->addDays($package->days),
            'user_id' => $user->id,
            'invoice_id' => $invoiceId,
            'amount' => $package->price,
            'status' => 'active'
        ];
        PremiumPayment::create($data);
        return response()->json([
            'status' => 'success'
        ]);
    }catch (\Exception $exception){

        return response()->json([
            'status' => 'failure'
        ]);

    }

    }

    private  function update_ispremium($user_id){
       try {
           $user = User::find($user_id);
           $user->update([
               'is_premium' => 1
           ]);
           return response()->json([
               'status' => 'success',
           ]);
       }catch (\Exception $exception){
           return response()->json([
               'status' => 'failure',

           ]);
       }
    }

    public function createPayment($userId, $packageId)
    {
        try {
            if (PremiumPayment::whereUserId($userId)->whereStatus('active')->exists()) {
                return response()->json([
                    'status' => 'already'
                ]);
            }

            $user = User::with(['location'])->where('id',$userId)->first();


            $package = PremiumPackage::find($packageId);
            $invoiceId = strtoupper(str_random(8) . '' . rand(1, 5));


            $response_create_user= $this->createPremiumUser($user, $invoiceId);

            if($response_create_user->getData()->status=="success"){
                $response_create_premium_payment= $this->createPremiumPayment($user, $package, $invoiceId);
                if($response_create_user->getData()->status =="success"){
                    $response_update_premium = $this->update_ispremium($userId);
                    return response()->json([
                        'status' => 'success',
                    ]);
                }
                else{
                    return response()->json([
                        'status' => 'failure',
                    ]);
                }
            }
            else{
                return response()->json([
                    'status' => 'failure',
                ]);
            }
        } catch (\Exception $exception) {

            return response()->json([
                'status' => 'failure',

            ]);
        }
    }

    public function get_prescriptions_using_points(Request $request, $user_id)
    {
        try {

            $user_points_data = DB::table("user_points")->where("user_id", '=', $user_id)->get();
            if ($user_points_data->count() == 0) {
                return response()->json(['status' => "failure", "message_bn" => "আপনার পর্যাপ্ত পরিমান মায়া পয়েন্ট নাই", "messange_en" => "unable to purchase due to insufficient points "]);
            } else {
                $total_point = $user_points_data[0]->total_points;
                $package_reqired = DB::table("point_sources")->where("id", "=", 9)->first();
                $total_point_required_to_buy = $package_reqired->point;
                $total_point_required_to_buy = abs($total_point_required_to_buy);
                if (PremiumPayment::whereUserId($user_id)->whereStatus('active')->exists()) {
                    return response()->json(['status' => "failure", "message_bn" => "আপনি অলরেডি মায়া প্যাস্ক্রিপশন প্যাকেজে আছেন", "messange_en" => "You are already maya prescription package subscriber"]);
                }


                if ($total_point_required_to_buy <= $total_point) {

                    $updated_total_point = $total_point - $total_point_required_to_buy;
                    $x = $this->createPayment($user_id, 5);
                    if ($x->getData()->status == "already") {
                        return response()->json(['status' => "failure", "message_bn" => "আপনি অলরেডি মায়া প্যাস্ক্রিপশন প্যাকেজে আছেন", "messange_en" => "You are already maya prescription package subscriber"]);
                    } elseif ($x->getData()->status == "success") {
//                        DB::table('user_points')
//                            ->where('user_id', "=", $user_id)
//                            ->update(['total_points' => $updated_total_point]);
                    event(new CreatePointTransaction($user_id, 9));
                        return response()->json(['status' => "success", "message_bn" => "আপনি সফল ভাবে মায়া প্যাস্ক্রিপশন প্যাকেজে সাবস্ক্রাইব করেছেন", "messange_en" => "You are successfully purchase maya prescription package"]);
                    } elseif ($x->getData()->status == "failure") {
                        return response()->json(['status' => "failure", "message_bn" => "ওহো কোন সমস্যা হয়েছে", "messange_en" => "Ops something went wrong"]);

                    }
                } else {
                    return response()->json(['status' => "failure", "message_bn" => "আপনার পর্যাপ্ত পরিমান মায়া পয়েন্ট নাই", "messange_en" => "unable to purchase due to insufficient points "]);
                }
            }
        }catch (\Exception $exception){
            return response()->json(['status' => "failure", "message_bn" => "ওহো কোন সমস্যা হয়েছে", "messange_en" => "Ops something went wrong"]);
        }
    }
}
