<?php

// Routes for Robi & Airtel
Route::group(['prefix' => 'v2/robi', 'exclude:csrf', 'middleware' => 'CheckClientCredentials'], function (){
    Route::post('store/question', 'Partners\Robi\QuestionController@storeRobiQuestion');
});
Route::group(['prefix' => 'multisource', 'middleware' => 'CheckClientCredentials'], function (){
    Route::get('robi/charging/{user_id}/{phone}/{package_id}', 'Robi\PaymentController@multisourceRobiCharging');
});
Route::group(['prefix' => 'v2/airtel', 'exclude:csrf', 'middleware' => 'CheckClientCredentials'], function (){
    Route::post('store/question', 'Partners\Robi\QuestionController@storeAirtelQuestion');
});

Route::group(['prefix' => 'v2/freebasics', 'exclude:csrf'], function (){
    Route::post('store/question', 'Partners\Freebasics\QuestionController@storeQuestion');
});

Route::group(['prefix' => 'v1/partners'], function (){
    Route::post('users/login', 'Partners\MayaPartenersController@login');
    Route::post('rate/answer/{question_id?}/{user_id?}', 'Partners\MayaPartenersController@rateAnswer');
    Route::post('post/question', 'Partners\MayaPartenersController@postQuestion');
    Route::post('fetch/questions/{offset?}/{limit?}', 'Partners\MayaPartenersController@fetchQuestions');
    Route::post('fetch/question-answer/{question_id?}', 'Partners\MayaPartenersController@getQuestionDetails');
    Route::post('fetch/my/questions/{user_id?}/{offset?}/{limit?}', 'Partners\MayaPartenersController@fetchMyQuestions');
    Route::post('fetch/articles/{language?}/{offset?}/{limit?}', 'Partners\MayaPartenersController@fetchArticle');
});

Route::group(['middleware' => 'CheckClientCredentials'], function (){

    Route::get('/settings','BanglaMedsController@GetSettings' );
    Route::get('/productlist','BanglaMedsController@GetProductList');
    Route::get('/districts','BanglaMedsController@GetDistrict');
    Route::get('/areas/{district_id}','BanglaMedsController@GetAreas');
    Route::get('/orderstatus/{id}','BanglaMedsController@GetOrderStatus');
    Route::get('/orderlist','BanglaMedsController@GetOrderList');
    Route::get('/order/{order}','BanglaMedsController@GetOrderHistory');

    Route::post('/customcheckout','BanglaMedsController@MakeCustomCheckout'); // pending


//    Route::get('/settings','BanglaMedsController@Settings' );
//    Route::post('users/login', 'Partners\MayaPartenersController@login');
//    Route::post('rate/answer/{question_id?}/{user_id?}', 'Partners\MayaPartenersController@rateAnswer');
//    Route::post('post/question', 'Partners\MayaPartenersController@postQuestion');
//    Route::post('fetch/questions/{offset?}/{limit?}', 'Partners\MayaPartenersController@fetchQuestions');
//    Route::post('fetch/question-answer/{question_id?}', 'Partners\MayaPartenersController@getQuestionDetails');
//    Route::post('fetch/my/questions/{user_id?}/{offset?}/{limit?}', 'Partners\MayaPartenersController@fetchMyQuestions');
//    Route::post('fetch/articles/{language?}/{offset?}/{limit?}', 'Partners\MayaPartenersController@fetchArticle');
});
