<?php

//maya-apa.com/mayaapi/api/v3/

Route::get('packages/{user_id?}/{nextPurchase?}', 'PremiumPackageController@getPackages_v3');

Route::group(['middleware' => 'CheckClientCredentials'], function (){

    Route::get('multisource/packages/robi/{user_id?}/{phone?}/{nextPurchase?}', 'PremiumPackageController@getPackages_multisource');
    // Premium status and package routes
    Route::group(['prefix' => 'premium'], function (){
        Route::get('package/list/{user_id?}/{nextPurchase?}', 'PremiumPackageController@getPackageList');
//        Route::get('status/{user_id}', 'PremiumPackageController@getPremiumStatus');
//        Route::get('payment/history/{user_id}', 'PremiumPackageController@getPaymentHistory');
        Route::get('packages/{user_id?}', 'PremiumPackageController@getPackages_v3');
        Route::get('unsubscribe/{user_id}/{package_id}', 'PremiumPackageController@unsubscribe');

        // Free Premium
        Route::get('free/{package_id}', 'PremiumPackageController@setFreePremium');
//        Route::get('chat/package/{user_id?}', 'PremiumPackageController@getChatPackage');
    });

    // Followup + socio-economic
    Route::group(['prefix' => 'followup'], function (){
        Route::get('get/history/{question_id}', 'FollowupController@getFollowUpHistory');
    });
    Route::group(['prefix' => 'socio-economic'], function (){
        Route::get('get/{user_id}', 'FollowupController@getSocioEconomicStatus');
        Route::get('create', 'FollowupController@createSocioEconomicQuestion');
        Route::get('update/{id}', 'FollowupController@updateSocioEconomicQuestion');
        Route::post('update', 'FollowupController@updateSocioEconomicStatus');
    });

    // Store question
    Route::post('store/question', 'QuestionController@storeQuestion');
    Route::get('user/last_question/{user_id}', 'UserProfilePageController@getLastQuestionStatus');


});



Route::post('get-answer/{question_id}', 'QuestionController@getAnswer');



