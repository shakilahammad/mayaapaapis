<?php

Route::get('query/correction', function () {

    $notification = new stdClass();

    $obj = (object) ['status' => "success", "source_title"=> 'sdfsdf',
        "source_sub_title"=>'sdfsd',"source_type"=>'sdfsdf',
        "action_type" => 'like' , "earned_point_for_the_action"=>55,
        "total_point"=>666, "current_batch"=>1 ,
        "is_badge_just_upgraded"=>false, "next_upper_badge"=>0
    ];

    $notification->notifiable = 301844;
    $notification->Message = $obj;

    \App\Classes\NotificationForUser::checkRecipientsForMayaPoints($notification);
//    dd($notification);

//    $user = \App\Models\User::find(25569);
//    $user->f_name = 'Maya';
//    $user->save();
//    return $user;
//    dd('hello');
//    $newQuestion = \App\Models\Question::find(1069954);
//
////    \App\Classes\Miscellaneous::trackSource($newQuestion);
//
//    $eta = new \App\Http\Controllers\APIs\V3\QuestionController();
//    list($responseTimeEn, $responseTimeBn) = $eta->getETA($newQuestion, 654400, 'question');
//
//    $eta->sendResponse([
//        'status' => 'Success',
//        'data' => $newQuestion,
//        'response_time_en' => $responseTimeEn,
//        'response_time_bn' => $responseTimeBn,
//        'error_code' => 0,
//        'error_message' => '',
//    ]);
//
//    $result = \App\Classes\Miscellaneous::callAIApi($newQuestion->id);
//    dd($result);
////        dump('0');
//    if(isset($result)) {
////            dump('1');
//        $data = json_decode($result);
//        $percent = isset($data->answer[0]->probability) ? $data->answer[0]->probability*100 : 0;
//        if(isset($data->answer) && $percent > 80) {
////            dd($percent);
//            $eta->autoAnswer($newQuestion, $data);
//        }
//    }
//
//    return $result;

//    $question = \App\Models\Question::find(1062705);
//    $result = \App\Classes\Miscellaneous::callAIApi($question->id);
//    $data = json_decode($result);
//    $ai = new \App\Listeners\Question\AutomationWork();
////    $ai->autoAnswer($question, $data->answer[0]->body);
//    dd(base64_encode(serialize($data->answer)));
//    $ai->createAiResponseLog($question->id, $data);

});

Route::get('test/push', function () {
    $user = \App\Models\User::find(16194);
    return $user;
//    $url = 'http://52.76.173.213/search_views_push/';
//
//    $curl = curl_init();
//    curl_setopt_array($curl, [
//        CURLOPT_RETURNTRANSFER => 1,
//        CURLOPT_FOLLOWLOCATION => true,
//        CURLOPT_CUSTOMREQUEST => "POST",
//        CURLOPT_URL => $url
//    ]);
//    $results = curl_exec($curl);
//    curl_close ($curl);
//
//    return $results;
});

Route::post('test/sql', function (\Illuminate\Http\Request $request) {
    $xmlData = $request->getContent();
    dd($xmlData);
});

Route::get('image/compress', function () {
//    $media = \App\Models\Medium::where('type', 'image')->get();
    $media = \App\Models\ProfilePicture::first();
    $m = $media;
//    dd($media->getUrlAttribute());
//    foreach ($media as $m) {
        // Getting file name
        $filename = $m->endpoint;
//dd($m->getUrlAttribute());
        // Valid extension
        $valid_ext = array('png','jpeg','jpg');

        // Location
        $location = "/images/userprofile/".$filename;
//        $location = $m->getUrlAttribute();

        // file extension
        $file_extension = pathinfo($m->getUrlAttribute(), PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);

        // Check extension
        if(in_array($file_extension,$valid_ext)){

            // Compress Image
            $p = new \App\Models\ProfilePicture();
            $pp = $p->compressImage($m->getUrlAttribute(),$location,60);
//            compressImage($filename.'_temp',$location,60);

        }else{
            echo "Invalid file type.";
        }
//    }
    return $pp;
});
Route::get('test/quiz/delete/{id}', function ($id) {
    $quiz_user = \App\Models\QuizUser::whereUserId($id)->first();
    $quiz_user->delete();
});

Route::get('/', function () {
    return view('home');
});

Route::get('test/user/phone', function () {
//    $phones = \App\Models\User::where('id', '525993')->get(['id', 'phone', 'email']);
//    $phones = \App\Models\User::whereIn('id', ['181680', '189867', '208733', '194551', '251567', '292883', '400623', '470675', '178167', '63288', '439612', '490706', '490835', '491980', '331291', '507505', '479838', '511287', '512946', '514579', '516495', '517838', '517941', '518000', '518010', '2826', '440916', '522846', '522903', '195848', '522999', '525746', '526003', '531201', '537939', '475018', '539614'])->get(['id', 'phone', 'email']);
    $phones = \App\Models\User::
            leftjoin('codes as c', 'c.referrer_id', '=', 'users.id')
            ->whereIn('c.code', ['rh8n8e', '9macbn', 'ywcqd6', 'abfx43', '8hyj4n', 'abfx43', '8hyj4n', 'abfx43', 'ytq17t', 'g8y6qc', 'vkerva', 'a9m1gm', 'vkerva', '6utfe2', 'mddj43', '9qq3dr', 't1y3un', '80yczm', 'gc4szd', '5gsuau', '5rpuh5', 'q8jcku', 't7fjnv', 'j01u2u', 'unsf36', '3wrgmn', '3wrgmn', '7xw96k', '2nybk5', '7xw96k', 'qx34pf', 'fpmcdb', 'a0mf1u', 'nwvp7j', 'wj004u', 'jds9q7', 'r3qgxh', 'jspqj6', '4h6chz', 'tfa7vc', 'ez8v4e', 'h60gna', 'we62pw', 'drnkpa', '95ud60', 'xbqqcb'])
            ->get(['users.id', 'c.code', 'users.phone', 'users.email']);
    foreach ($phones as $phone){
//        if($phone->phone != null ){
            $data[$phone->code]['id'] = $phone->id;
            $data[$phone->code]['id'] = $phone->id;
            $data[$phone->code]['phone'] = $phone->phone;
            $data[$phone->code]['email'] = $phone->email;
//        }
    }
    return $data;
});

Route::get('suscribe/notification/test', function () {
        $payment = \App\Models\PremiumPayment::find(18000);
//        dd($payment);
        \App\Classes\Subscriptions\PremiumSubscription::success($payment);
});

Route::group(['prefix' => 'premium'], function () {
    Route::get('{package_id}/{lan?}', 'PackageController@index');
});

Route::get('/expert/profile/{userId}', 'ExpertProfileController@expertProfile');

//Route::get('/expert/profile/{userId}', function ($userId){
//   return view('expertProfile')->with([ "userId" => $userId]);
//});

Route::get('/maya/prescription/{question_id}', 'PrescriptionController@getPrescription');

Route::get('/maya/daily_ramadan/', 'RamadanController@dailyRamadan');
Route::get('/maya/ramadan_checkins/{user_id}', 'RamadanController@dailyRamadanCheckIn');

Route::group(['middleware' => 'CheckClientCredentials'], function (){
    Route::get('ramadan_checkin/auth', 'RamadanController@RamadanCheckInAuth');
});

Route::get('test/treading', function (){
   return view('threading');
});

Route::post('fetch/questions/{type}/{offset?}/{limit?}/{direction?}/{order?}/{status?}/{user_id?}', 'QuestionController@fetchQuestionStream');



