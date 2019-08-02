<?php

// maya-apa.com/mayaapi/api/v1

Route::get('test', function (){
    dd("hello");
//    $data = DB::select(DB::raw("SELECT DISTINCT (user_id), phone, created_at FROM premium_users WHERE user_id IN ( SELECT DISTINCT (user_id) FROM premium_payments WHERE STATUS = 'expired' AND expiry_time < '2018-10-20 23:59:59' ) ORDER BY created_at DESC LIMIT 500"));
//    foreach ($data as $d){
//        if(!empty($d->phone))
//        $phone[] = $d->phone;
//    }
//    dd(implode(',', $phone));

//    $userInfo = PremiumUser::whereInvoiceId(1)->first();
//    $package = PremiumPackage::find(1);
//    $payment = \App\Models\PremiumPayment::find(48);
//
//    if (filter_var($userInfo->email, FILTER_VALIDATE_EMAIL) == true) {
//        try {
//            \Mail::to($userInfo->email)->queue(new PaymentSuccess($payment, $package, $userInfo));
//        }catch (Exception $exception){
//            dd($exception->getMessage());
//        }
//    }

//    $payment = \App\Models\PremiumPayment::whereInvoiceId(1)->first();
//    $userInfo = \App\Models\PremiumUser::whereInvoiceId(1)->first();
//    $package = \App\Models\PremiumPackage::find(1);
//
//    if (filter_var($userInfo->email, FILTER_VALIDATE_EMAIL) == true) {
//        \Mail::to($userInfo->email)->queue(new \App\Mail\PaymentSuccess($payment, $package, $userInfo));
//    }

//    \App\Models\Question::create([
//        'body' => 'again',
//        'user_id' => 7178,
//        'source' => 'app',
//        'is_premium' => 1
//    ]);

//    $premiumUser = \App\Models\PremiumPayment::whereUserId(7178)->whereStatus('active')->first();
//
//    $premiumUser->increment('question_count');

//    if ($question->isPremium() && $premiumUser->exists()){
//        $premiumUser->increment('question_count');
////      PremiumPayment::whereUserId($asker->id)->whereStatus('active')->increment('question_count');
//    }

//    \App\Models\PremiumPayment::create([
//         'user_id' => 5571,
//         'package_id' => 1,
//         'status' => 'active',
//         'invoice_id' => 1,
//         'effective_time' => \Carbon\Carbon::now(),
//         'expiry_time' => \Carbon\Carbon::now()->addDays(7),
//         'created_at' => \Carbon\Carbon::now(),
//    ]);
});

// New login
//Route::post('login/{provider}', 'LoginController@login');
Route::post('logout', 'LoginController@logout');

Route::get('packages/{user_id?}', 'PremiumPackageController@getPackageListOld');
Route::post('store/question', 'QuestionController@storeQuestion');

//Route::post('followup-question', 'FollowupController@storeFollowUpQuestion');
Route::get('followup-status/{question_id}/{lang?}', 'FollowupController@getFollowUpStatus');
Route::post('followup-status/', 'FollowupController@updateFollowUpStatus');
Route::get('get/followup/history/{question_id}', 'FollowupController@getFollowUpHistory');
Route::group(['middleware' => 'CheckClientCredentials'], function (){
    // Premium status and package routes
    Route::group(['prefix' => 'premium'], function (){
        Route::get('user/info/{user_id}', 'PremiumPackageController@fetchPremiumUserInfo');
        Route::get('status/{user_id}', 'PremiumPackageController@getPremiumStatus');
        Route::get('payment/history/{user_id}', 'PremiumPackageController@getPaymentHistory');
        Route::get('packages/{user_id?}', 'PremiumPackageController@getPackageList');
        Route::get('package/list/{user_id?}', 'PremiumPackageController@getCustomPackageList');
        Route::get('user/stat/{user_id}', 'UserStatusController@index');
    });

    // Promo codes
    Route::get('apply/promo/code/{code}/{user_id}', 'PromoCodeController@apply');
    Route::get('applied/promo/{user_id}', 'PromoCodeController@getAppliedPromo');

    Route::post('user/question/list/{offset?}/{limit?}/{direction?}/{order?}/{user_id?}', 'QuestionController@fetchUsersQuestion');
    Route::get('user/last_question/{user_id}', 'UserMatricesController@getLastQuestionStatus');
    Route::get('media/{media_id}','QuestionController@fetchMedia');

    Route::post('hide-question/{question_id}/{user_id}', 'QuestionController@hideQuestion');
    Route::post('save-question/{question_id}/{user_id}', 'QuestionController@saveQuestion');
    Route::post('saved-question/{offset}/{limit}/{direction}/{order}/{user_id}', 'QuestionController@fetchSavedQuestion');

    Route::get('notifications/{user_id}/{status}/{skip}/{take}/{lan?}','NotificationController@getNotifications');
    Route::get('followup/notification/{user_id}/{language?}', 'NotificationController@getFollowupNotification');

    Route::get('user/matrices/{id}', 'UserMatricesController@getMatrices');
    Route::post('edit/profile','ProfileController@update');

    Route::get('reply/{comment_id}', 'CommentAndReplyController@fetchReply');
    Route::post('make/comment/spam/{userId}/{commentId}', 'CommentAndReplyController@spamComment');
    Route::post('post/comment/or/reply', 'CommentAndReplyController@postCommentAndReply');

//    Route::post('store/question', 'QuestionController@storeQuestion');
    Route::post('parent/questions/{question_id}/{user_id?}','QuestionController@parentQuestions');

    Route::get('questions/{question}/followup', 'FollowupController@index');
//    Route::get('get/followup/history/{question_id}', 'FollowupController@getFollowUpHistory');
    Route::post('followup-question', 'FollowupController@storeFollowUpQuestion');

    Route::post('like/{question_id}/{user_id}','LikeController@like');
    Route::post('questions/rate','RatingController@storeRate');
    Route::get('check/israted/{user_id}','RatingController@checkIsRated');

    // Freemium
    Route::get('get-code/{user_id}', 'FreemiumController@getCode');
    Route::post('receives/invitation', 'FreemiumController@receivesInvitation');

    // Scratch card routes
    Route::get('apply-card/{card_number}/{user_id}','ScratchCardController@applyScratchCard');
    Route::get('check-purchase-status/{user_id}','ScratchCardController@checkStatus');
    Route::get('get-payment-history/{user_id}','ScratchCardController@get_payment_history');

    // Literacy measue routes
    Route::group(['prefix' => 'literacy'], function (){
        Route::get('get/mcqs/{user_id}/{question_id}', 'LiteracyMeasureController@fetchMCQs');
        Route::post('submit/answer/{user_id}/{question_id}', 'LiteracyMeasureController@storeAnswer');
        Route::get('cancel/mcqs/{user_id}/{question_id}/{mcq_id?}', 'LiteracyMeasureController@cancel');
    });

    // Non-Premium user's question count
    Route::get('user/question/cap/{user_id}', 'UserStatusController@questionCapCount');
    Route::get('user/free_premium/question/cap/{user_id}', 'UserStatusController@freePremiumquestionCapCount');

});

Route::post('store/gcm', 'AuthController@storeGCM');
//Route::post('login','AuthController@login');
//Route::post('register','AuthController@register');
//Route::post('reset/password','AuthController@resetPassword');
//Route::post('check/user/credentials', 'AuthController@checkEmailOrPhone');
//Route::post('facebook/login','AuthController@facebookLogin');
//Route::post('email-login/','AuthController@loginWithEmail');
//Route::post('phone-login/','AuthController@loginWithPhone');
//Route::post('user/logout','AuthController@logout');


Route::get('comment/reply/{question_id}', 'CommentAndReplyController@fetchCommentAndReply');
Route::get('get/current/version/{package_name}', 'UserMatricesController@getLatestVersionNumberApp');
Route::post('send/feedback', 'UserMatricesController@feedback');
Route::get('get/remaining-media/{user_id}', 'UserMatricesController@freeMedia');
Route::post('track/download', 'UserMatricesController@track_user_download');


Route::post('get-answer/{question_id}', 'QuestionController@getAnswer');
Route::get('get-suggested-questions/{question_id}', 'QuestionController@getQuestionSuggestion');
Route::post('fetch/questions/{offset?}/{limit?}/{direction?}/{order?}/{status?}/{user_id?}', 'QuestionController@fetchQuestionStream');

Route::group(['prefix' => 'dynamic/card'], function (){
    Route::get('{user_id?}', 'DynamicCardController@index');
    Route::get('cancel/{card_id}/{user_id}', 'DynamicCardController@cancelCard');
});

Route::get('get-inviteContent', 'FreemiumController@getContent');
Route::get('insert-eligibles', 'FreemiumController@insertEligibleusers');
Route::get('get-invite-count/{user_id}','FreemiumController@getInviteCount');

Route::get('get-advertisements/{type}', 'AdvertisementController@getAdvertisements');
Route::post('store-advertisements', 'AdvertisementController@store_adds');

// Articles routes
Route::post('fetch/articles/{language?}/{offset?}/{limit?}/{category?}', 'ArticleController@fetchArticles');
Route::post('search/articles/{offset?}/{limit?}/{keyword?}', 'ArticleController@searchArticles');
Route::post('fetch/article/{articleId}', 'ArticleController@fetchSingleArticle');

Route::post('get/feedback/message/{Id}/{userId?}', 'UserFeedbackController@getFeedbackMessage');
Route::post('post/feedback/{questionId}/{userId}', 'UserFeedbackController@postFeedback');

Route::get('send/push', 'UserMatricesController@sendPushToSomeUsers');

//Get id
Route::get('get-awarded-users/','UserMatricesController@getUserInfo');

// push notification received logs
Route::post('notification_received', 'PushNotificationReceiveController@postReceivedPushNotification');
Route::get('push/search', 'PushNotificationReceiveController@callPushAction');

Route::get('/earning_sources', 'MayaPoints@earning_source');

Route::post('/create_transactions', 'MayaPoints@create_transaction');
Route::get("/get_all_transaction_history/{userId}", "MayaPoints@get_all_transaction_history");
Route::get("/get_user_total_point_and_badge/{userId}","MayaPoints@get_user_total_point_and_badge");
Route::get("/get_prescriptions_using_points/{userId}","MayaPoints@get_prescriptions_using_points");
//Route::post("/subscribe_by_points/{userID}")