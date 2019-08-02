<?php

namespace App\Http\Controllers\ApiV1;

use App\Models\PremiumCoupon;
use App\Models\PremiumCouponApplied;
use App\Models\TrackDownload;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Invite;
use App\Models\GcmUser;
use App\Models\Freemium;
use App\Models\InviteCode;
use App\Models\Subscribers;
use EmailChecker\EmailChecker;
use Illuminate\Http\Request;
use App\Models\RewardMessage;
use App\Models\AppSubscribers;
use App\Models\EligibleForReward;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Classes\PushNotificationToUserApp;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\In;

class FreemiumController extends Controller
{
    public function getCode($user_id)
    {
        $code = InviteCode::whereReferrerId($user_id)->whereType('invite')->where('exp_date', '>', Carbon::now())->first();

        if (count($code) > 0) {
            return response()->json($code);
        }

        $code = InviteCode::create([
            'code' => $this->random_str(6),
            'type' => 'invite',
            'referrer_id' => $user_id,
            'title' => 'invite',
            'exp_date' => Carbon::now()->addYears(3)
        ]);

        return response()->json($code);
    }

    public function receivesInvitation(Request $request)
    {
        $blocked_referrer_ids = [
            116223,
            526141,
            620543,
            621617,
            205108,
            526139,
            606999,
            621860,
            223512
        ];


//        dd($request->header());
//        return response()->json([
//            'status' => 'success',
//            'days' => 0,
//            'reason' => 'ইনভাইটের সময় শেষ। শীঘ্রই নতুন ক্যাম্পেইন আসছে।',
//            'error_code' => 0,
//            'error_message' => '',
//        ]);

        try{

            $date = Carbon::now()->month;

            $total_invite = Invite::where('recipient_id', $request->user_id)->get();

            $is_applied_before_current_month = false;


            if(count($total_invite) >= 1) {
                // can apply
                $is_applied_before_current_month = Invite::where('recipient_id', $request->user_id)->whereRaw(" month(updated_at) = $date")->orderBy('updated_at', 'desc')->exists() ? true : false;
            }

            if($is_applied_before_current_month){

                return response()->json([
                    'status' => 'success',
                    'days' => 0,
                    'reason' => 'এই একাউন্ট থেকে আপনি এই মাসে ইতিমধ্যে ইনভাইট কোড প্রয়োগ করেছেন',
                    'error_code' => 0,
                    'error_message' => '',
                ]);
            }

            $received_code = $request->received_code;

            $user_id = $request->user_id;
            $code = InviteCode::whereCode($received_code)->where('exp_date', '>', Carbon::now())->first();
//        $referrer_gcm = GcmUser::where('user_id', $code->referrer_id)->first();
            $recipient_gcm = GcmUser::where('user_id', $user_id)->first();

            $device_id_1 = User::where('id', '=', $user_id)->first();

            if(isset($code))
                $device_id_2 = User::where('id', '=', $code->referrer_id)->first();

            if(isset($code) && in_array($code->referrer_id, $blocked_referrer_ids)){
                return response()->json([
                    'status' => 'failure',
                    'reason' => 'Seems like you are faking it!!!',
                    'days' => 0,
                    'error_code' => 0,
                    'error_message' => '',
                ]);
            }


//            $same_device = GcmUser::where('gcm_id', $recipient_gcm->gcm_id)
//                ->join('invites', 'gcm_users.user_id', '=' ,'invites.recipient_id')
//                ->get();
//            dd($request->user_id);
            $same_user = Invite::where('recipient_id', $request->user_id)->get();
//            dd($same_user);
            $track_download = TrackDownload::where('id', $device_id_1->track_download_id)->first();

            $same_device = Invite::join('users as u', 'u.id', '=', 'invites.recipient_id')
                ->join('track_download as td', 'td.id', '=', 'u.track_download_id')
                ->where('td.device_id', $track_download->device_id)
                ->select('u.*')
                ->get();

//            dd($same_device);

//            $gcm_1 = GcmUser::where('user_id', $code->referrer_id)->first();
//        $gcm_2 = GcmUser::where('user_id', $user_id)->first();



            $scout = in_array($received_code, ['drnkpa',
                '3dz5gh','02bfgg','w2unsq', 'z5qnbe', 'tcugnc', '7f08eu',
                '0zbm1b', 'ac9jr6', 'k7fg3e', 's9gb1t', 'e6dg15',
                'm3y5g3',
                'm1mv8d' ,
                '7r6m96' ,
                'gdk522' ,
                'aftr78' ,
                'y9r6w7' ,
                'y95bx9' ,
                'zump28' ,
                'hnvggv' ,
                'db386t' ,
                '9x0tcr' ,
                'dyj3w9' ,
                'bb496j' ,
                '9sm0p3',
                'xyk862',
                '9nq8xg',
                'mjz8pj',
                '4kr6tp',
                'axdbxc',
                'uafhcg',
                'hmp3hb']);

//            $email_checker = new EmailChecker();

//            $email_checker_status = $email_checker->isValid($device_id_1->email);

            $valid_email_domain = [
                'gmail.com',
                'yahoo.com',
                'outlook.com',
                'msn.com',
                'live.com',
                'ymail.com',
                'yandex.ru',
                'facebook.com',
//                'emailay.com',
//                'phone.com.bd'
            ];

            $email_checker_status = false;

            foreach ($valid_email_domain as $domain){
                $email_checker_status = $this->endsWith($device_id_1->email, $domain);
                if($email_checker_status === true){
                    break;
                }
            }

//            dd(count($same_user) <1);

            $phone_number_status = $device_id_1->phone ? strpos($device_id_1->phone, '880') : -1;

//            dd($device_id_1->phone);
//            dd($phone_number_status == 0);
//              dd($email_checker_status);
//            dump(count($same_user) <1);
//            dump($same_device);
//            dd($email_checker_status,$device_id_1->email, $device_id_1->phone, $phone_number_status );


            if (($email_checker_status || $phone_number_status === 0 )
//                && count($same_user) <1
                && count($code) > 0
//                && count($same_device) < 1
                && $device_id_1->track_download_id != $device_id_2->track_download_id &&
//                $gcm_1->gcm_id != $recipient_gcm->gcm_id &&
                $device_id_1->track_download_id != 1 ) {

                if ($code->referrer_id != $user_id) {
                    $isInvited = false;

                    if ($code->type == 'invite') {

                        $already_received = Invite::whereRecipientId($user_id)->with('codes')->get();

                        foreach ($already_received as $received) {
                            if ($received->codes->type == 'invite') {
                                $isInvited = true;
                            }
                        }
                        if ($isInvited) {

                            $invite = Invite::firstOrCreate(["recipient_id" => $user_id, "code_id" => $code->id,],
                                ["session" => 1, 'exp_date' => Carbon::now()->addDays(3) ]);

//                            $invite = Invite::whereRecipientId($user_id)->where('code_id', $code->id)->first();

                            $invite->updated_at = Carbon::now();
                            $invite->save();
                            return response()->json([
                                'status' => 'success',
                                'reason' => 'Thank You!! Invite code Applied',
                                'days' => 0,
                                'error_code' => 0,
                                'error_message' => '',
                            ]);

                        } else {

                            $session = count(User::whereId($user_id)->whereSession(1)->first());
                            Invite::create([
                                'code_id' => $code->id,
                                'recipient_id' => $user_id,
                                'session' => $session,
                                'exp_date' => Carbon::now()->addDays(3)
                            ]);

//                            $this->applyReward($code, $user_id);

//                            Log::emergency('user_id '.$user_id . ' '. 'code : '.$code->id . 'referrer_id: '. $code->referrer_id. ' '. json_encode($request->header()));

                            return response()->json([
                                'status' => 'success',
                                'days' => 1,
                                'reason' => 'Thank You!! Invite code Applied',
                                'error_code' => 0,
                                'error_message' => '',
                            ]);
                        }

                    } else {
                        //promo code
                        $already_received = Invite::with('codes')->whereRecipientId($user_id)->whereCodeId($code->id)->get();
                        if (count($already_received) > 0) {
                            return response()->json([
                                'status' => 'failure',
                                'reason' => 'Invalid code Applied',
                                'days' => 0,
                                'error_code' => 0,
                                'error_message' => '',
                            ]);
                        } else {
                            Invite::create([
                                'code_id' => $code->id,
                                'recipient_id' => $user_id,
                                'exp_date' => Carbon::now()->addDays($code->days)
                            ]);

                            return response()->json([
                                'status' => 'success',
                                'days' => $code->days,
                                'reason' => 'Thank You!! You will be notified soon',
                                'error_code' => 0,
                                'error_message' => '',
                            ]);
                        }
                    }

                } else {
                    return response()->json([
                        'status' => 'failure',
                        'reason' => 'Sorry!! Own Code Applied',
                        'days' => 0,
                        'error_code' => 0,
                        'error_message' => '',
                    ]);
                }
            } else {

                if(!count($code)){
                    $reason = 'Invalid code';
//                    dd('1');
                }
                elseif($device_id_1->track_download_id == 1){
                    $reason = 'দয়া করে আবার লগইন করুন';
                }elseif(count($same_user) > 0){
//                    dd($same_user);
                    $reason = 'এই একাউন্ট থেকে আপনি ইতিমধ্যে ইনভাইট কোড প্রয়োগ করেছেন';
                }
                elseif ( !count($same_device) < 1 || !($device_id_1->track_download_id != $device_id_2->track_download_id)
//                    || !($gcm_1->gcm_id != $recipient_gcm->gcm_id)
                ){
//                    dump(!count($same_device) < 1);
//                    dump(!($device_id_1->track_download_id != $device_id_2->track_download_id));
//                    dd(count($same_device));
                    $reason = 'এই ফোন থেকে ইতিমধ্যে ইনভাইট কোড প্রয়োগ করা হয়েছে';
//                    dd('2');
                }
                elseif(!$email_checker_status || !($phone_number_status == 0)){
                    $reason = 'Your phone or email is invalid ';
//                    dd('3');
                }
                else{
//                    dd('4');
                    $reason = 'Invalid code';
                }


                return response()->json([
                    'status' => 'failure',
                    'reason' => $reason,
                    'days' => 0,
                    'error_code' => 0,
                    'error_message' => '',
                ]);

            }
        }catch (\Exception $exception){

            $reason = 'something went wrong';

            if(!isset($code) || !isset($device_id_2)) {
                $reason = 'Invalid code';
            }

//            Log::emergency( 'user_id: '. $user_id . ' code: :' . $code . ' ' .$exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine() );

            return response()->json([
                'status' => 'failure',
                'reason' => $reason,
                'days' => 0,
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

    }

    private function applyReward($code, $recipientId)
    {
        $couponHundredPercent = PremiumCoupon::whereType('invite')->whereDiscount(100)->first();
        $couponFiftyPercent = PremiumCoupon::whereType('invite')->whereDiscount(50)->first();

        if (count($couponHundredPercent) && count($couponFiftyPercent)) {
            $this->createPromoApplied($code->referrer_id, $couponHundredPercent->id);

            $appliedPromo = PremiumCouponApplied::withTrashed()->whereCouponId(50)->whereUserId($recipientId)->count();

            if (!$appliedPromo) {
                $this->createPromoApplied($recipientId, $couponFiftyPercent->id);
            }
        }
    }

    private function createPromoApplied($recipientId, $couponId)
    {
        PremiumCouponApplied::create([
            'coupon_id' => $couponId,
            'user_id' => $recipientId
        ]);
    }

    public function enableFreemium($invite_id, $user_id, $number_of_days, $phone_number)
    {
        $active_subscription = Freemium::whereUserId($user_id)->get()->first();
        if (count($active_subscription) > 0) {
            $active_subscription->delete();
        }
        $active_subscription = Freemium::create([
            'invite_id' => $invite_id,
            'user_id' => $user_id,
            'status' => 'active',
            'exp_date' => $number_of_days == 1 ? Carbon::now()->addDay(1) : Carbon::now()->addDays(3)
        ]);

        $subscribers_id = $this->addToSUbscribers($phone_number);
        $this->subscribeUser($user_id, $subscribers_id, $active_subscription->exp_date, $number_of_days == 1 ? 1 : 3);
        //send push notification to both
    }

    public function addToSUbscribers($phone_number)
    {
        $phone = ltrim($phone_number, '+');
        $result = Subscribers::where("phone_number", "like", "%{$phone}%")->first();
        if (count($result) > 0) {
            return $result->id;
        } else {
            $result = Subscribers::create([
                'phone_number' => $phone,
                'status' => 1
            ]);
            return $result->id;
        }
    }

    function random_str($length, $keyspace = '0123456789abcdefghjkmnpqrstuvwxyz')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public function subscribeUser($user_id, $subscribers_id, $exp, $plan)
    {

        $already_subscribed = AppSubscribers::whereUsersId($user_id)->first();
        if (count($already_subscribed) > 0) {
            $already_subscribed->update([
                'expiry_time' => Carbon::parse($already_subscribed->expiry_time)->addDays($plan),
                'status' => 'active'
            ]);
            //send noti
            try {
                if ($plan == 9) {

                    $this->send_notification($user_id, 1);
                } else {
                    $this->send_notification($user_id, 3);
                }
            } catch (\Exception $exception) {

            }

        } else {
            AppSubscribers::create([
                'users_id' => $user_id,
                'status' => 'active',
                'source' => 'app',
                'subscribers_id' => $subscribers_id,
                'app_subscription_plans_id' => $plan == 1 ? 9 : 8,
                'effective_time' => Carbon::now(),
                'expiry_time' => $exp
            ]);
            //send noti
            try {
                if ($plan == 9) {

                    $this->send_notification($user_id, 1);
                } else {
                    $this->send_notification($user_id, 3);
                }
            } catch (\Exception $exception) {

            }

        }


    }

    public function send_notification($user_id, $day)
    {

        if ($day == 1) {
            $data = [
                'detail' => "Congratulations !!!  You can now use Maya Apa Premium service free for next 24 hours. Try it now!!!",
                'subject' => "Enjoy Maya Apa Premium!!",
                'message' => "Congratulations !!!  You can now use Maya Apa Premium service free for next 24 hours. Try it now!!!"

            ];
        } else {
            $data = [
                'detail' => "Congratulations !!!  You can now use Maya Apa Premium service free for next 3 days. Try it now!!!",
                'subject' => "Enjoy Maya Apa Premium!!",
                'message' => "Congratulations !!!  You can now use Maya Apa Premium service free for next 3 days. Try it now!!!"

            ];
        }


        $gcmUser = GcmUser::whereUserId($user_id)->first();
        if (count($gcmUser)) {
            // Send push notification to user app
            $post = [
                'registration_ids' => [$gcmUser->gcm_id],
                'data' => $data
            ];
            PushNotificationToUserApp::sendGCMNotification($post);
        }


    }

    public function getInviteCount($user_id)
    {

//        $results = DB::select(DB::raw("select users.id,count(*) as count from users,invites,codes where users.id = codes.`referrer_id` and invites.session =1 and invites.created_at between '2019-06-21 00:00:00' and '2019-06-27 11:59:59' and invites.`code_id` = codes.id and users.session = 1 and users.id = $user_id "));
        $results_second_phase = DB::select(DB::raw("select users.id,count(*) as count from users,invites,codes where users.id = codes.`referrer_id` and invites.session =1 and ((invites.created_at between '2019-07-19 12:00:00' and '2019-08-01 11:59:59') or (invites.updated_at between '2019-07-19 12:00:00' and '2019-08-01 11:59:59') ) and invites.`code_id` = codes.id and users.session = 1 and users.id =$user_id "));

//        dd("select users.id,count(*) as count from users,invites,codes where users.id = codes.`referrer_id` and invites.session =1 and ((invites.created_at between '2019-07-07 12:00:00' and '2019-07-16 11:59:59') or (invites.updated_at between '2019-07-07 12:00:00' and '2019-07-16 11:59:59') ) and invites.`code_id` = codes.id and users.session = 1 and users.id = $user_id");
//        if (count($results))
//            $total_previous = $results[0]->count;
//        else
//            $total_previous = 0;

        if (count($results_second_phase))
            $total = $results_second_phase[0]->count;
        else
            $total = 0;


//        $taka_prev = (int)($total_previous/4) * 20;
//        $leftover = $total_previous%4;
//        $possible_taka_prev = $taka_prev + 20;

//        $taka = (int)(($total+$leftover)/4) * 20;
//        dump($leftover);
//        $required_invite = (4 - (($total+$leftover)%4) ?? 4);
//        $possible_taka = $taka + 20;

//        dump($total);
//        dump($leftover);
//        $taka = 0;
//        $required_invite = 0;
//        $possible_taka = 0;

        $ticket = $total * 5;

        return response()->json([
            'status' => "success",
//            'total_invited_en' => "ইনভাইট ক্যাম্পেইনে অংশগ্রন করার সময় শেষ।\nবর্তমান ইনভাইটঃ " . ($total + $leftover). "\n সর্বমোট টাকা : " . $taka . "৳\n" ."যাচাই বাছাই চলছে। শীঘ্রই ভ্যালিড ইনভাইটকারিদের টাকা পাঠিয়ে দেওয়া হবে।",
            'total_invited_en' => "আপনি " .$ticket. " টি টিকেট পেয়েছেন।\nপরবর্তী লটারির ড্র 01-08-2019\nবিঃদ্রঃ আগের ইনভাইট করা বন্ধুদেরকেও ইনভাইট করতে পারবেন!",
//            'total_invited_bn' => "সর্বমোট ভ্যালিড ইনভাইট ঃ " . $total . " অথবা " . (int)($total/4) * 20 . " taka" . " - " . "2019-06-11 16:00:00 তারিখের পর থেকে ।"
//            'total_invited_bn' => "ইনভাইট ক্যাম্পেইনে অংশগ্রন করার সময় শেষ।\nবর্তমান ইনভাইটঃ " . ($total + $leftover). "\n সর্বমোট টাকা : " . $taka . "৳\n" ."যাচাই বাছাই চলছে। শীঘ্রই ভ্যালিড ইনভাইটকারিদের টাকা পাঠিয়ে দেওয়া হবে।",
            'total_invited_bn' => "আপনি " .$ticket. " টি টিকেট পেয়েছেন।\nপরবর্তী লটারির ড্র 01-08-2019\nবিঃদ্রঃ আগের ইনভাইট করা বন্ধুদেরকেও ইনভাইট করতে পারবেন!",
        ]);

    }

    public function sendPush($user_id)
    {
        //Send Push Notification by checking on eligible list
        $eligible_user = EligibleForReward::whereUserId($user_id)->whereIsEligible(0)->get();

        if (count($eligible_user) > 0) {
            $eligible_user->first()->is_eligible = 1;
            $eligible_user->first()->update();
            $message = RewardMessage::find($eligible_user->first()->message_id);
            $data = [
                'detail' => $message->message,
                'subject' => "Enjoy your Reward from Maya Apa!!",
                'message' => $message->message

            ];

            $gcmUser = GcmUser::whereUserId($user_id)->first();
            if (count($gcmUser)) {
                // Send push notification to user app
                $post = [
                    'registration_ids' => [$gcmUser->gcm_id],
                    'data' => $data
                ];
                PushNotificationToUserApp::sendGCMNotification($post);
            }
        } else {
            $data = [
                'detail' => "মায়া আপাকে ২ টির বেশি ভ্যালিড প্রশ্ন জিজ্ঞেস করলে আপনি এই অফারের আওতাভুক্ত হবেন । ",
                'subject' => "অফারের  তথ্য",
                'message' => "মায়া আপাকে ২ টির বেশি ভ্যালিড প্রশ্ন জিজ্ঞেস করলে আপনি এই অফারের আওতাভুক্ত হবেন । "

            ];

            $gcmUser = GcmUser::whereUserId($user_id)->first();
            if (count($gcmUser)) {
                // Send push notification to user app
                $post = [
                    'registration_ids' => [$gcmUser->gcm_id],
                    'data' => $data
                ];
                PushNotificationToUserApp::sendGCMNotification($post);
            }
        }
    }

    public function getContent()
    {
        return response()->json([
            'status' => 'success',
            'header_en' => 'ইনভাইট করুন । ',
            'header_bn' => 'ইনভাইট করুন । ',
            'subheader_en' => 'পুরস্কার জিতুন ',
            'subheader_bn' => 'পুরস্কার জিতুন ',
            'title_en' => 'Mi Power Bank জিতুন!',
            'title_bn' => 'Mi Power Bank জিতুন!',
//            'subtitle_en' => ' মায়া পরিবারে আপনার পছন্দের মানুষদেরকে ইনভাইট করুন !! </b> ভালো থাকুন সবাইকে নিয়ে । ইনভাইট করতে নিচের বাটনটি চাপুন ।',
//            'subtitle_bn' => ' মায়া পরিবারে আপনার পছন্দের মানুষদেরকে ইনভাইট করুন !! </b> ভালো থাকুন সবাইকে নিয়ে । ইনভাইট করতে নিচের বাটনটি চাপুন ।',
//            'subtitle_en' => "মায়া আপা অ্যাপে রেফার করে আপনার বন্ধুকে মায়া প্লাসের যে কোনো প্যাকেজে উপহার দিন ৫০% ছাড়। </br> আর তাদের এক্টিভেশনের পর আপনি উপভোগ করুন ১০০% ছাড়!!",
//            'subtitle_en' => "প্রতি ৪ জনকে ইনভাইটের মাধ্যমে মায়া পরিবারে যোগ করে <b>জিতে নিন ২০ টাকা ফ্লেক্সিলোড</b> । যত খুশি তত বার ।  </b> ভালো থাকুন সবাইকে নিয়ে । ইনভাইট করতে নিচের বাটনটি চাপুন ।",
            'subtitle_en' => "প্রতি ইনভাইটেই ৫ টিকেট আর যত বেশি টিকেট তত বেশি Mi Power Bank জেতার সুযোগ!<br>গত সপ্তাহের Mi Smart Band বিজয়ী Toufikul Islam (Id: 601368)",
//            'subtitle_en' => "ইনভাইট ক্যাম্পেইনটি স্থগিত করা হয়েছে। আগামীকাল (২৩.০৬.২০১৯) বিকাল ৪ টা থেকে আবার শুরু করা হবে।",
//            'subtitle_bn' => "ইনভাইট ক্যাম্পেইনটি স্থগিত করা হয়েছে। আগামীকাল (২৩.০৬.২০১৯) বিকাল ৪ টা থেকে আবার শুরু করা হবে।",
            'subtitle_bn' => "প্রতি ইনভাইটেই ৫ টিকেট আর যত বেশি টিকেট তত বেশি Mi Power Bank জেতার সুযোগ!<br>গত সপ্তাহের Mi Smart Band বিজয়ী Toufikul Islam (Id: 601368)",
//            'subtitle_bn' => "প্রতি ৪ জনকে ইনভাইটের মাধ্যমে মায়া পরিবারে যোগ করে <b>জিতে নিন ২০ টাকা ফ্লেক্সিলোড</b> । যত খুশি তত বার ।  </b> ভালো থাকুন সবাইকে নিয়ে । ইনভাইট করতে নিচের বাটনটি চাপুন ।",
//            'subtitle_en' => "ইনভাইট ক্যাম্পেইনটি সাময়িক স্থগিত আছে। অংশ গ্রহণকারিদের ফ্লেক্সিলোড দেওয়ার পর ক্যাম্পেইনটি শীঘ্রই চালু করা হবে।",
//            'subtitle_bn' => "ইনভাইট ক্যাম্পেইনটি সাময়িক স্থগিত আছে। অংশ গ্রহণকারিদের ফ্লেক্সিলোড দেওয়ার পর ক্যাম্পেইনটি শীঘ্রই চালু করা হবে।",
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function insertEligibleusers()
    {
        $users = [
            5000,
            45696,
            29885,
            10245,
            28795,
            3900,
            27163,
            43089,
            995,
            12083,
            15914,
            55859,
            18000,
            15982,
            12037,
            27359,
            9281,
            54600,
            5007,
            35105,
            28177,
            13287,
            39444,
            15037,
            12969,
            29999,
            26515,
            12951,
            6867,
            18821,
            9140,
            25283,
            14122,
            13248,
            25680,
            21005,
            11605,
            13358,
            2284,
            39851,
            9008,
            7539,
            12344,
            29630,
            18447,
            29474,
            6413,
            13611,
            15581,
            11823,
            25496,
            33922,
            18673,
            51936,
            61498,
            15023,
            35529,
            1249,
            12554,
            25699,
            12634,
            36161,
            25604,
            29428,
            11285,
            41120,
            22888,
            21182,
            22961,
            27140,
            14653,
            18127,
            49855,
            11898,
            21369,
            57880,
            51687,
            13878,
            28761,
            28419,
            11396,
            11512,
            22258,
            17730,
            1743,
            6225,
            31946,
            19864,
            29828,
            37829,
            56914,
            29545,
            18093,
            12244,
            56033,
            5571,
            57796,
            11046,
            17909,
            17665,
            17456,
            15918,
            11522,
            10183,
            19419,
            17432,
            26690,
            43224,
            1097,
            10374,
            24325,
            2379,
            28414,
            27132,
            23115,
            30498,
            24889,
            27302,
            50113,
            18234,
            11944,
            17383,
            20581,
            10216,
            28612,
            4088,
            29959,
            40904,
            12512,
            62157,
            23186,
            29097,
            39901,
            29848,
            50393,
            10982,
            26663,
            46782,
            30646,
            55004,
            27959,
            42987,
            30505,
            39801,
            27750,
            30411,
            30447,
            11425,
            29421,
            12873,
            13689,
            41990,
            56144,
            58394,
            27999,
            28443,
            1247,
            11488,
            1156,
            15895,
            39470,
            14464,
            37968,
            12156,
            20913,
            33362,
            26459,
            5189,
            13507,
            19949,
            21797,
            14294,
            44022,
            7362,
            17169,
            1740,
            28757,
            49005,
            7063,
            2555,
            12626,
            14127,
            26645,
            15938,
            5040];
        foreach ($users as $user) {
            EligibleForReward::insert([
                'user_id' => $user,
                'message_id' => 1,
                'is_eligible' => 1
            ]);
        }
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return substr($haystack, -strlen($needle))===$needle;
    }

}
