<?php

use Illuminate\Support\Facades\Route;
use Modules\FcmCentral\Api\v1\EvenementFCMController;
use Modules\FcmCentral\Api\v1\HistoriqueFCMController;
use Modules\FcmCentral\Api\v1\ParcoursFCMController;


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

Route::middleware(['forcejson', 'auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('postevent', [EvenementFCMController::class, 'postevent'])->name('post-event');
    Route::get('get_marin_history/{uuid}', [HistoriqueFCMController::class, 'get_marin_history'])->name('get_marin_history');
    Route::get('parcours', [ParcoursFCMController::class, 'index'])->name('get_parcours_list');
    Route::get('parcours/{parcours}', [ParcoursFCMController::class, 'description'])->name('get_parcours_description');
    Route::get('tous_parcours_serialises', [ParcoursFCMController::class, 'get_tous_parcours_serialises'])->name('get_tous_parcours_serialises');
    
    
});
