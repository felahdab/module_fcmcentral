<?php

use Illuminate\Support\Facades\Route;
use Modules\FcmCentral\Api\v1\EventController;
use Modules\FcmCentral\Api\v1\ParcoursController;


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
    Route::get('get_user_history/{uuid}', [EventController::class, 'get_user_history'])->name('get_user_history');
    Route::get('parcours', [ParcoursController::class, 'index'])->name('get_parcours_list');
    Route::get('parcours/{parcours}', [ParcoursController::class, 'description'])->name('get_parcours_description');
    
});
