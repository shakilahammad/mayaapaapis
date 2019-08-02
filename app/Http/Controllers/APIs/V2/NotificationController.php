<?php

namespace App\Http\Controllers\APIs\V2;

use App\Models\PremiumCoupon;
use App\Models\PromoNotification;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use http\Env\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller 
{
    public function getNotifications($user_id, $status, $skip = 0, $take = 10, $language = 'en')
    {
        $user = User::find($user_id);

        if(count($user)){
            $notifications = $user->notifications()->whereIsSeen(0)->skip($skip)->take($take)->orderBy('updated_at', 'desc')->get();

            if (count($notifications)) {
                $data = $this->formattedNotification($notifications, 'v3', $language, $user_id);

                return response()->json([
                    'status' => 'success',
                    'data' => $data,
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }
        }

        return response()->json([
            'status' => 'failure',
            'data' => [],
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

    public function getFollowupNotification($userId, $language = 'bn')
    {
        $notifications = Notification::where('notifiable', $userId)->where('notifications_message_id', 27)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        if (count($notifications)) {
            return response()->json([
                'status' => 'success',
                'data' => $this->formattedNotification($notifications, 'v4', $language, $userId),
                'next_url' => $notifications->nextPageUrl(),
                'total' => $notifications->total(),
                'error_code' => 0,
                'error_message' => ''
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'data' => [],
            'next_url' => null,
            'total' => 0,
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

    public function formattedNotification($notifications, $apiVersion, $language, $user_id)
    {
        $data = [];

        $promo_notifications = PromoNotification::whereRaw('user_id = '.$user_id.' AND expiry_time >= NOW()')->take(10)->get();

        if (count($promo_notifications)>0){
            $data = $this->formattedPromoNotification($promo_notifications, 'v3');
        }

        foreach ($notifications as $notification) {
            if ($apiVersion == 'v2'){
                $createdAt = Carbon::parse($notification->updated_at)->toDateTimeString();
            }else{
                $createdAt = Carbon::parse($notification->updated_at)->diffForHumans();
            }

            if(isset($language) && $language == 'bn') {
                $title = empty($notification->Message->title_bn) ? $notification->Message->title : $notification->Message->title_bn;
                $details = empty($notification->Message->details_bn) ? $notification->Message->details : $notification->Message->details_bn;
            }else{
                $title = $notification->Message->title;
                $details = $notification->Message->details;
            }

            if($notification->Message->type == 'reply_question'){

                $notification->Message->type = 'Comment';
                $details = $notification->notifier_id == 45468 ? 'An expert from Maya has replied to a comment on your question.' : $details;

            }else if($notification->Message->type == 'reply_comment'){
                $details = $notification->notifier_id == 45468 ? 'An expert from Maya has replied to your comment on a question.' : $details;
                $notification->Message->type = 'Comment';
            }

            $values = [
                'id' => $notification->id,
                'type' => $notification->Message->type,
                'user_id' => $notification->notifier_id,
                'question_id' => $notification->question_id,
                'title' => $notification->notifier_id == 45468 ? 'Expert Replied' : $title,
                'detail' => $details,
                'count'=>$notification->count,
                'seen' => $notification->is_seen,
                'action_data' => '',
                'created_at' => $createdAt,
            ];

            array_push($data, $values);
        }

        return $data;
    }

    public function formattedPromoNotification($notifications, $apiVersion)
    {
        $data = [];
        foreach ($notifications as $notification) {
            if ($apiVersion == 'v2'){
                $createdAt = Carbon::parse($notification->updated_at)->toDateTimeString();
            }else{
                $createdAt = Carbon::parse($notification->updated_at)->diffForHumans();
            }

//            if(isset($language) && $language == 'bn') {
//                $title = empty($notification->Message->title_bn) ? $notification->Message->title : $notification->Message->title_bn;
//                $details = empty($notification->Message->details_bn) ? $notification->Message->details : $notification->Message->details_bn;
//            }else{
//                $title = $notification->title;
//                $details = $notification->detail;
//            }

            $values = [
                'id' => $notification->id,
                'type' => $notification->type,
                'user_id' => $notification->user_id,
                'question_id' => '',
                'title' => $notification->title,
                'detail' => $notification->detail,
                'count'=>0,
                'seen' =>0,
                'action_data' => $notification->action_data,
                'created_at' => $createdAt,
            ];

            array_push($data, $values);
        }

        return $data;
    }

//    public function createPromoNotification($notifiable, $type, $title, $detail, $action_data, $class_name)
    public function createPromoNotification()
    {
        $params = request()->all();

        try {
            $notifications_message_id = 35; // Need to make it dynamic later
            $coupon = PremiumCoupon::select('expiry_time')->where('code', '"'.$params['action_data'].'"')->where('expiry_time', '>', 'NOW()')->first();
            if(!isset($coupon)){
                $couponRaw = DB::select("select `expiry_time` from `premium_coupons` where code = '".$params['action_data']."' and `expiry_time` > NOW() and `premium_coupons`.`deleted_at` is null");
                $coupon = $couponRaw[0];
            }
            $newNotification = PromoNotification::updateOrCreate(
                ['user_id' => $params['user_id'], 'notifications_message_id' => $notifications_message_id, 'action_data' => $params['action_data']],
                [
                    'user_id' => $params['user_id'],
                    'notifications_message_id' => $notifications_message_id,
                    'type' => $params['type'],
                    'title' => $params['title'],
                    'detail' => $params['detail'],
                    'action_data' => $params['action_data'],
                    'class_name' => $params['class_name'],
                    'expiry_time' => $coupon->expiry_time
                ]
            );

            if (!$newNotification->wasRecentlyUpdated) {
                $newNotification->increment('count');
            }

            return response()->json([
                'status' => 'success',
                'data' => $newNotification
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failure',
                'message' => [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ]
            ]);
        }
    }
}
