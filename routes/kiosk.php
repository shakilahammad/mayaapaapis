<?php

Route::post('/store/gcm', 'KioskController@storeGCM');
Route::post('user/question/list/{offset?}/{limit?}/{direction?}/{order?}/{user_id?}', 'KioskController@fetchUsersQuestion');
Route::post('phone-login/','KioskController@loginWithPhone');
Route::post('store-question/','KioskController@storeQuestion');
Route::post('get-answer/{question_id}', 'KioskController@getAnswer');
Route::post('phone-login-pass/','KioskController@loginWithPhoneAndPassword');
Route::post('phone-registration-pass/','KioskController@registerWithPhoneAndPassword');
Route::post('reset-password/','KioskController@reset_password');
