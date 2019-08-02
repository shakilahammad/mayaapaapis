<?php

// maya-apa.com/mayaapi/api/v2/

//Route::post('login/{provider}', 'LoginController@login');
Route::post('location/login/test', 'LoginController@loginLocation');
Route::post('logout', 'LoginController@logout');
Route::get('fake/login/{userId}', 'FakeLoginController@fake');

Route::get('packages/{user_id?}/{nextPurchase?}', 'PremiumPackageController@getPackages');
Route::get('followup-status/{question_id}/{lang?}', 'FollowupController@getFollowUpStatus');
Route::post('followup-status/', 'FollowupController@updateFollowUpStatus');
Route::get('get/followup/history/{question_id}', 'FollowupController@getFollowUpHistory');
Route::get('get/followup/list/{user_id}', 'FollowupController@getFollowUpList');

Route::post('followup-question', 'FollowupController@storeFollowUpQuestion');
Route::post('fetch/questions/{offset?}/{limit?}/{direction?}/{order?}/{status?}/{user_id?}', 'QuestionController@fetchQuestionStream');

Route::post('login/{provider}', 'LoginController@login');
Route::post('/poco/login/{provider}', 'LoginController@loginPOCO');

Route::group(
    ['middleware' => 'CheckClientCredentials'],
    function (){
    // Premium status and package routes
    Route::group(['prefix' => 'premium'], function (){
        Route::get('status/{user_id}', 'PremiumPackageController@getPremiumStatus');
        Route::get('payment/history/{user_id}', 'PremiumPackageController@getPaymentHistory');
        Route::get('packages/{user_id?}', 'PremiumPackageController@getPackages');
        Route::get('chat/package/{user_id?}', 'PremiumPackageController@getChatPackage');
    });

    // Store question
    Route::post('store/question', 'QuestionController@storeQuestion');

    // Store Phone number
    Route::post('store/phone', 'PremiumController@store');

    Route::get('user/last_question/{user_id}', 'UserMatricesController@getLastQuestionStatus');
    // Notifications
    Route::post('promo_notifications','NotificationController@createPromoNotification');
    Route::get('notifications/{user_id}/{status}/{skip}/{take}/{lan?}','NotificationController@getNotifications');
    Route::get('followup/notification/{user_id}/{language?}', 'NotificationController@getFollowupNotification');

    Route::post('user/question/list/{status?}/{offset?}/{limit?}/{direction?}/{order?}/{user_id?}', 'QuestionController@fetchUsersQuestion');


//Get id
    Route::get('get-awarded-users/','ApiV1\UserMatricesController@getUserInfo');

});

Route::get('get-suggested-questions/{lang}/{question_id}', 'QuestionController@getQuestionSuggestion');
Route::post('notification_received', 'PushNotificationReceiveController@postReceivedPushNotification');

Route::post('get-answer/{question_id}', 'QuestionController@getAnswer');