<?php

use Illuminate\Support\Facades\Route;
use Modules\FcmCentral\Api\v1\EventController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('postevent', [EventController::class, 'postevent'])->name('post-event');
    Route::post('get_user_uuid', [EventController::class, 'get_user_uuid'])->name('get_user_uuid');
    Route::post('get_user_history', [EventController::class, 'get_user_history'])->name('get_user_history');
    
});
