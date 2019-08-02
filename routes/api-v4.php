<?php

// maya-apa.com/mayaapi/api/v4/

Route::get('packages/{user_id?}/{nextPurchase?}', 'PremiumPackageController@getPackages_v4');
Route::get('giveaway/product', 'GiveawayController@getUserGiveawayProduct');
Route::get('giveaway/history', 'GiveawayController@getUserGiveawayHistory');
Route::group(['prefix' => 'premium'], function (){
    Route::get('status/{user_id}', 'PremiumPackageController@getPremiumStatus_v4');
});

/*
|--------------------------------------------------------------------------
| Quiz Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'app/quiz'], function (){
    Route::post('get', 'QuizController@getQuiz'); // api/v4/app/quiz/get
    Route::post('re/get', 'QuizController@getReQuiz'); // api/v4/app/quiz/re/get
    Route::post('submit', 'QuizController@submitQuiz'); // api/v4/app/quiz/submit
});

Route::post('get-answer/{question_id}', 'QuestionController@getAnswer');
//Route::get('packages/{user_id?}/{nextPurchase?}', 'PremiumPackageController@setFreePremium');

Route::group(['middleware' => 'CheckClientCredentials'], function () {
    Route::get('prescription/{prescription_id}/', 'PrescriptionController@getPrescription');
    Route::get('giveaway/status/{userId}', 'GiveawayController@getUserGiveawayStatus');
    Route::get('giveaway/entry/{userId}/{product_id}', 'GiveawayController@getUserGiveawayEntry');
});