<?php
/**
 * Created by PhpStorm.
 * User: razib
 * Date: 2019-07-03
 * Time: 17:26
 */


Route::group(['middleware' => 'CheckClientCredentials'], function () {
    Route::post('store/question', 'QuestionController@storeQuestion');
    Route::get('check-session-status/{user_id}/{session}', 'PocoController@CheckSessionStatus');
    Route::get('get-new-session/{user_id}', 'PocoController@GetNewSession');
});