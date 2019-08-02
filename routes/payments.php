<?php

// maya-apa.com/mayaapi/

Route::group(['prefix' => 'payment/portwallet', 'middleware' => 'CheckClientCredentials'], function (){
    Route::post('new/invoice/create', 'PortwalletController@createInvoice');
    Route::post('invoice/create', 'PortwalletController@createInvoice');
    Route::post('invoice/status', 'PortwalletController@verifyTransaction');
    Route::post('invoice/retrieve', 'PortwalletController@retrieveTransaction');
    Route::post('invoice/refund', 'PortwalletController@refundRequest');
});

Route::group(['prefix' => 'payment/portwallet'], function (){
    Route::get('response', 'PortwalletController@getResponsePortwallet');
    Route::post('ipn', 'PortwalletController@ipnStatus');
});

Route::group(['prefix' => 'payment/bkash'], function (){
    Route::get('/new/checkout/{userId?}/{packageId?}', 'Bkash\BkashController@checkoutNew');
    Route::get('checkout/{userId?}/{packageId?}', 'Bkash\BkashController@checkout');
    Route::post('checkout/{userId}/{packageId}', 'Bkash\BkashController@createPayment');
    Route::post('execute/{userId}/{packageId}', 'Bkash\BkashController@executePayment');

    Route::get('success', 'Bkash\BkashController@paymentSucess');
});

Route::group(['prefix' => 'payment/free'], function (){
    Route::post('invoice/create', 'FreePremiumController@createInvoice');
});

Route::group(['prefix' => 'payment/gp', 'middleware' => 'CheckClientCredentials'], function (){
    Route::get('access/token', 'GP\GPPayment@getAccessToken');
    Route::get('push_otp/{phone}/{package_id}/{user_id?}', 'GP\GPPayment@gpPushOTP');
    Route::get('charge/{access_token}/{trasactionId}/{phone}/{pin}/{package_id}/{user_id?}', 'GP\GPPayment@gpCharge');
});