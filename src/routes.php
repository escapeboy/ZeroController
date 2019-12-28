<?php

use \Illuminate\Support\Facades\Route;

Route::get('call/{class}/{method}', [\ZeroController\Controllers\Api\CallController::class, 'call'])
     ->name('api.call')
     ->middleware('auth:api');
