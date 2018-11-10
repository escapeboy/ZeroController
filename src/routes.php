<?php

use \Illuminate\Support\Facades\Route;

Route::get('call/{class}/{method}', 'Api\CallController@call')
     ->name('api.call')
     ->middleware('auth:api');