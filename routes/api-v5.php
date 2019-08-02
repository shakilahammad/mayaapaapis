<?php

// maya-apa.com/mayaapi/api/v5/

Route::group(['prefix' => 'premium'], function (){
    Route::get('status/{user_id}', 'PremiumPackageController@getPremiumStatus_v5');
});


Route::post('get-answer/{question_id}', 'QuestionController@getAnswer');