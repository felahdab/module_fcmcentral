<?php

use Illuminate\Support\Facades\Route;
use Modules\FcmCentral\Http\Controllers\FcmCentralController;
use Modules\FcmCentral\Http\Controllers\ExportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/fcm_debug2', function(){
//     return' FcmCentral routes ok';
// })->name('fcmcentral.debug2');

Route::group([], function () {
    //Route::resource('fcmcentral', FcmCentralController::class)->names('fcmcentral');
    Route::get('/fcmcentral/competences/export-csv', [ExportController::class, 'exportCompetencesCsv'])
    ->name('fcmcentral.competences.export_csv');

Route::get('/fcmcentral/annees/export-csv', [ExportController::class, 'exportAnneesCsv'])
    ->name('fcmcentral.annees.export_csv');

});
