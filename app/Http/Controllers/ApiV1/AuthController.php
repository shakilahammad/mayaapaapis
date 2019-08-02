<?php

namespace App\Http\Controllers\ApiV1;

use Carbon\Carbon;
use App\Http\Helper;
use App\Models\GcmUser;
use App\Models\User;
use Illuminate\Http\Request;
use App\Classes\SetLocation;
use App\Models\TrackDownload;
use App\Models\AppSubscribers;
use League\Flysystem\Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Classes\PushNotificationToUserApp;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        date_default_timezone_set('Asia/Dhaka');
        $post_data = $request->input();

        if (isset($post_data['email']) && !empty($post_data['email'])) {
            $credentials = [
                'email' => $post_data['email'],
                'password' => $post_data['password']
            ];
        } else {
            $result = DB::select(DB::raw("select phone, email from users where phone like '%{$post_data['phone']}%'"));
            if ($result) {
                $credentials = [
                    'email' => $result[0]->email,
                    'password' => $post_data['password']
                ];
            } else {
                $credentials = [
                    'email' => $post_data['phone'],
                    'password' => $post_data['password']
                ];
            }
        }

        try {
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $user->session = 0;
                $user->save();
                $user->is_premium = AppSubscribers::whereUsersId($user->id)->exists() ? 1 : 0;
                return response()->json([
                    'status' => 'success',
                    'data' => $user,
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'data' => [],
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }

        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failure',
                'data' => [],
                'error_code' => 0,
                'error_message' => ''
            ]);
        }
    }

    public function register(Request $request)
    {
        $user_data = $request->input();

        if (isset($user_data['phone']) && (!empty($user_data['phone']))) {
            if (User::wherePhone($user_data['phone'])->exists()) {
                return response([
                    'status' => 'exist',
                    'data' => [],
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }
        }

        if (isset($user_data['email']) && (!empty($user_data['email']))) {
            if (User::whereEmail($user_data['email'])->exists()) {
                return response([
                    'status' => 'exist',
                    'data' => [],
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }
        }


        // get user IP
        if (isset($user_data['ip'])) {
            $ip = $user_data['ip'];
        } else {
            $ip = $request->ip();
        }
        $user_data['ip'] = $ip;

        $user_data['lat'] = (isset($user_data['lat']) && !empty($user_data['lat'])) ? $user_data['lat'] : '';
        $user_data['long'] = (isset($user_data['long']) && !empty($user_data['long'])) ? $user_data['long'] : '';
        $user_data['f_name'] = !(isset($user_data['f_name'])) ? 'Anonymous' : $user_data['f_name'];
        $user_data['l_name'] = !(isset($user_data['l_name'])) ? '' : $user_data['l_name'];
        $user_data['birthday'] = !(isset($user_data['dob'])) ? '' : Carbon::parse($user_data['dob'])->format('Y-m-d');
        $user_data['email'] = !(isset($user_data['email'])) ? $user_data['phone'] . '@phone.com.bd' : $user_data['email'];
        $user_data['phone'] = !(isset($user_data['phone'])) ? '' : $user_data['phone'];
        $user_data['source'] = !(isset($user_data['source'])) ? 'web' : $user_data['source'];
        $user_data['session'] = 0;
        $user_data['registered'] = 0;
        $salt = 'asdfasfdasf';
        $user_data['password'] = sha1($salt . $user_data['password']);

        try {
            $createUser = User::create($user_data);

            if (isset($user_data['device_id'])) {
                $track_download_id = TrackDownload::whereDeviceId($user_data['device_id'])->first();
                if (count($track_download_id)) {
                    $createUser->track_download_id = $track_download_id->id;
                    $createUser->save();
                }
            }

            Auth::login($createUser);

            $user_data['id'] = $createUser->id;
            $user_data['joindate'] = date('d-m-Y h:i:s', strtotime($createUser->created_at));
            return response([
                'status' => 'success',
                'data' => $user_data,
                'error_code' => 0,
                'error_message' => ''
            ]);
        } catch (\Exception $exception) {
            return response([
                'status' => 'failure',
                'data' => [],
                'error_code' => 0,
                'error_message' => ''
            ]);
        }
    }

    public function facebookLogin(Request $request)
    {
        $email = empty($request->email) ? $request->fb_id . '@facebook.com' : $request->email;
        $user = User::where('email', Helper::maya_encrypt($email))->first();
        $is_new = 0;
        if (count($user)) {
            $user->session = 0;
            $user->registered = 1;
            $user->fb_id = $request->fb_id;
            $user->save();
            $user->is_new = $is_new;
            $user->location = '';
            $user->phone = 'secured_phone';
            $user->email = 'secured_email';
            $user->fb_id = 'secured_fb_id';
            $user->f_name = 'Anonymous';
            $user->l_name = 'Anonymous';
            return response()->json(['sataus' => 'success', 'user' => $user]);
        } else {
            $is_new = 1;
            $download_id = 0;
            if (isset($request->device_id)) {
                $track_download_id = TrackDownload::whereDeviceId($request->device_id)->first();
                if (count($track_download_id)) {
                    $download_id = $track_download_id->id;
                    //$createUser->save();
                }
            }
            $location = SetLocation::formattedLocation($request->ip(), $request->lat, $request->long, $user->id);
            $userData = [
                'f_name' => empty($request->f_name) ? 'Anonymous' : $request->f_name,
                'l_name' => empty($request->l_name) ? '' : $request->l_name,
                'email' => $email,
                'fb_id' => $request->fb_id,
                'gender' => empty($request->gender) ? '' : $request->gender,
                'birthday' => empty($request->birthday) ? null : $request->birthday,
                'source' => empty($request->source) ? '' : $request->source,
                'location_id' => $location->id,
                'age' => Carbon::parse($request->birthday)->age,
                'track_download_id' => $download_id,
                'registered' => 1,
                'session' => 0
            ];

            $user = User::create($userData);
            $user = User::find($user->id);
            $user->phone = 'secured_phone';
            $user->email = 'secured_email';
            $user->fb_id = 'secured_fb_id';
            $user->f_name = 'Anonymous';
            $user->l_name = 'Anonymous';
            $user->is_new = $is_new;
            $user->location = '';

            return response()->json(['sataus' => 'success', 'user' => $user, 'error_code' => 0,
                'error_message' => '']);
        }
    }

    public function resetPassword(Request $request)
    {
        $userData = $request->all();

        if (isset($userData['email']) && isset($userData['password'])) {
            $user = User::whereEmail($userData['email'])->first();
            if (!count($user)) {
                return response([
                    'status' => 'not exist',
                    'data' => [],
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }
        }

        if (isset($userData['phone']) && isset($userData['password'])) {
            $user = User::wherePhone($userData['phone'])->first();
            if (!count($user)) {
                return response([
                    'status' => 'not exist',
                    'data' => [],
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }
        }

        try {
            $password = bcrypt($userData['password']);
            $user->password = $password;
            $user->save();
            return response()->json([
                'status' => 'success',
                'data' => $user,
                'error_code' => 0,
                'error_message' => ''
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failure',
                'data' => [],
                'error_code' => 0,
                'error_message' => ''
            ]);
        }
    }

    public function sendPushToUsers($user_id)
    {
        ini_set('max_execution_time', 0);
        //type = custom

        // task = url / answer / activity/fragment

        //class_name =
        //for answer(com.maya.mayaapaapp.Activities.Generic.AnswerDetailsActivityFromNotification) /
        //for activity (whatever activity we want to open with package name)/
        //for fragment(Any Fragments that can be open from Main Activity(ask_questions_fragment/ NotificationFragment/MayaApaPlusFragment) with package name)

        // url = redirect when click on button

        // log_in_needed = no

        //  image_url = creative file url on notification page

        // question_id = when it is needed to open a specific question

        $data = [
            "subject" => "মায়া আপাতে আপনাকে স্বাগতম !",
            "message" => "আপনার প্রথম প্রশ্নের উত্তর নিন মাত্র ৩০ মিনিটে ! পরিচয় জানবেনা কেও । ",
            "noti_type" => "custom",
            "noti_task" => "fragment",
            "class_name" => "com.maya.mayaapaapp.Fragments.ask_questions_fragment",
            "url" => "",
            "header_Text" => "মায়া আপাতে আপনাকে স্বাগতম !",
            "details_text" => "আপনার প্রথম প্রশ্নের উত্তর নিন মাত্র ৩০ মিনিটে ! পরিচয় জানবেনা কেও । ",
            "btn_text" => "প্রশ্ন করুন এখনই ",
            "log_in_needed" => 'no',
            "image_url" => "https://image.ibb.co/nGtHzn/icon_welcome.png",
            "question_id" => "33"
        ];
        $gcmUser = GcmUser::where("user_id",$user_id)->get();
        if (count($gcmUser)) {
            foreach ($gcmUser as $user) {
                // Send push notification to user app

                try{
                    $post = [
                        'registration_ids' => [$user->gcm_id],
                        'data' => $data
                    ];
                    PushNotificationToUserApp::sendGCMNotification($post);
                }catch (\Exception $exception){

                }

            }
        }

    }

    public function storeGCM(Request $request)
    {
        if (isset($request->user_id) && isset($request->gcm_id)) {
            $fcm = DB::table('gcm_users')
                ->where('user_id', $request->user_id)
                ->get();
            if (count($fcm)) {
                DB::table('gcm_users')
                    ->where('user_id', $request->user_id)
                    ->update([
                        'gcm_id' => $request->gcm_id
                    ]);
            } else {
                try{
                DB::table('gcm_users')
                    ->where('user_id', $request->user_id)
                    ->insert([
                        'user_id' => $request->user_id,
                        'gcm_id' => $request->gcm_id
                    ]);

                $this->sendPushToUsers($request->user_id);

                }catch (Exception $exception){

                }
            }
            $response = [
                'status' => 'success',
                'error_code' => 0,
                'error_message' => ''
            ];
        } else {
            $response = [
                'status' => 'failure',
                'error_code' => 0,
                'error_message' => ''
            ];
        }
        return response()->json($response);
    }

    public function checkEmailOrPhone(Request $request)
    {
        $userData = $request->all();

        if (isset($userData['email'])) {
            $user = User::whereEmail(Helper::maya_encrypt($userData['email']))->first();
            if (!count($user)) {
                return response([
                    'status' => 'failure',
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            } else {
                return response([
                    'status' => 'success',
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }
        } else if (isset($userData['phone'])) {
            $user = DB::select(DB::raw("select phone from users where phone like '%{$userData['phone']}%'"));
            //$user = User::wherePhone($userData['phone'])->first();
            if (!$user) {
                return response([
                    'status' => 'failure',
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            } else {
                return response([
                    'status' => 'success',
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }
        }
    }

    public function logout(Request $request)
    {
        date_default_timezone_set('Asia/Dhaka');
        $id = !empty($request->id) ? $request->id : 0;
        $user = User::find($id);
        if (count($user)) {

            Auth::logout();

            // Delete Session for this user
            $session_id = Session::getId();
            if (!empty($session_id)) {
                DB::select("delete from sessions where id = '$session_id'");
            }
            $user->session = 0;
            $user->save();
            $response = [
                'status' => 'success',
                'error_code' => 0,
                'error_message' => ''
            ];
        } else {
            $response = [
                'status' => 'failure',
                'error_code' => 0,
                'error_message' => ''
            ];
        }
        return response()->json($response);
    }

    public function loginWithEmail(Request $request)
    {
        ini_set('display_errors', 1);
        date_default_timezone_set('Asia/Dhaka');
        $post_data = $request->input();

        $user = null;
        if (isset($post_data['email']) && !empty($post_data['email'])) {

            $user = User::where('email', Helper::maya_encrypt($post_data['email']))->first();
            $is_new = 0;
        }

//        try{
        if (!$user) {
            $user = $this->registerWithEmail($post_data);
            $user = User::whereId($user->id)->first();
            $is_new = 1;
        }
        if ($user) {
            //Auth::login($user);
            $user->session = 0;
            $user->save();
            $user->is_premium = AppSubscribers::whereUsersId($user->id)->exists() ? 1 : 0;
            $user->is_new = $is_new;
            $user->location = '';
            $user->phone = 'secured_phone';
            $user->email = 'secured_email';
            $user->fb_id = 'secured_fb_id';
            $user->f_name = 'Anonymous';
            $user->l_name = 'Anonymous';
            return response()->json([
                'status' => 'success',
                'data' => $user,
                'error_code' => 0,
                'error_message' => ''
            ]);
        }

//        }catch (\Exception $exception){
//            dd( $exception);
//            return response()->json([
//                'status' => 'failure',
//                'data'   => []
//            ]);
//        }
        return response()->json([
            'status' => 'failure',
            'data' => [],
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

    public function loginWithPhone(Request $request)
    {
        date_default_timezone_set('Asia/Dhaka');
        $post_data = $request->input();

        $user = null;
        if (isset($post_data['phone']) && !empty($post_data['phone'])) {
            $user_phone = preg_replace('/[^0-9]/', '', $post_data['phone']);
//            $user_phone = ltrim($post_data['phone'],'+');
//            $user_phone = ltrim($post_data['phone'],' ');

            $user = User::where('phone',Helper::maya_encrypt($user_phone))->orWhere('email',Helper::maya_encrypt($user_phone.'@phone.com.bd'))->first();
            $is_new = 0;
        }

        try {
            if (!$user) {
                $user = $this->registerWithPhone($request);
                $user = User::find($user->id);
                $is_new = 1;
            }
            if ($user) {
                //Auth::login($user);
                $user->session = 0;
                $user->save();
                $user->is_premium = AppSubscribers::whereUsersId($user->id)->exists() ? 1 : 0;
                $user->is_new = $is_new;
                $user->location = '';
                $user->phone = 'secured_phone';
                $user->email = 'secured_email';
                $user->fb_id = 'secured_fb_id';
                $user->f_name = 'Anonymous';
                $user->l_name = 'Anonymous';
                return response()->json([
                    'status' => 'success',
                    'data' => $user,
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }

        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failure',
                'data' => [$exception->getMessage(), $exception->getLine()],
                'error_code' => 0,
                'error_message' => ''
            ]);
        }
        return response()->json([
            'status' => 'failure',
            'data' => [],
            'error_code' => 0,
            'error_message' => ''
        ]);
    }


    public function registerWithEmail($post_data)
    {
        if (isset($post_data['lat']) && isset($post_data['long']) && $post_data['long']!=0 && $post_data['lat']!=0) {
            $lat = $post_data['lat'];
            $long = $post_data['long'];
        } else {
            $lat = 23.991734;
            $long = 90.419588;
        }

        $location = SetLocation::formattedLocation(0,$lat,$long );

        $user_data['email']    = $post_data['email'];
        $user_data['source']   = !(isset($post_data['source'])) ? 'app' : $post_data['source'];
        $user_data['session']  = 1;
        $user_data['registered']  = 1;
        $user_data['location_id']  = $location->id;



        try{
            if(isset($post_data['device_id'])){
                $track_download_id = TrackDownload::whereDeviceId($post_data['device_id'])->first();
                if(count($track_download_id)){
                    $user_data['track_download_id'] = $track_download_id->id;
                    //$createUser->save();
                }
            }
            $createUser = User::create($user_data);
            $createUser = User::find($user_data->id)->first;
            $location->user_id = $createUser->id;
            $location->save();
            $createUser->location = '';
            return $createUser;

        }catch (\Exception $exception){
            return null;
        }
    }

    public function registerWithPhone($post_data)
    {

        if (isset($post_data['lat']) && isset($post_data['long']) && $post_data['long']!=0 && $post_data['lat']!=0) {
            $lat = $post_data['lat'];
            $long = $post_data['long'];
        } else {
            $lat = 23.991734;
            $long = 90.419588;
        }
        $location = SetLocation::formattedLocation(0,$lat,$long);
        $phone_number = ltrim($post_data['phone'],'+');
        $phone_number = ltrim($post_data['phone'],' ');

        $user_data['phone']    = $phone_number;
        $user_data['email']    =  $phone_number . '@phone.com.bd';
        $user_data['source']   = !(isset($post_data['source'])) ? 'app' : $post_data['source'];
        $user_data['session']  = 1;
        $user_data['registered']  = 1;
        $user_data['location_id']  = $location->id;



        try{
            if(isset($post_data['device_id'])){
                $track_download_id = TrackDownload::whereDeviceId($post_data['device_id'])->first();
                if(count($track_download_id)){
                    $user_data[' '] = $track_download_id->id;
                    //$createUser->save();
                }
            }
            $createUser = User::create($user_data);
            $createUser = User::find($createUser->id);
            $location->user_id = $createUser->id;
            $location->save();
            $createUser->location = '';




            return $createUser;

        }catch (\Exception $exception){
            return null;
        }
    }

}
