<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('dashboard', [\App\Http\Controllers\SlipsController::class, 'index'])->name('dashboard');
    Route::get('createSlip', [\App\Http\Controllers\SlipsController::class, 'createSlip'])->name('createSlip');
    Route::post('/analyze-bet-screenshot', [\App\Http\Controllers\SlipsController::class, 'analyzeBetScreenshot'])->name('analyzeBetScreenshot');
    Route::post('storeSlip', [\App\Http\Controllers\SlipsController::class, 'storeSlip'])->name('storeSlip');
    Route::patch('change-slip-status/{slip}', [\App\Http\Controllers\SlipsController::class, 'changeSlipStatus'])->name('changeSlipStatus');
    Route::patch('update-slip-stake/{slip}', [\App\Http\Controllers\SlipsController::class, 'updateSlipStake'])->name('updateSlipStake');
    Route::patch('toggle-slip-played/{slip}', [\App\Http\Controllers\SlipsController::class, 'toggleSlipPlayed'])->name('toggleSlipPlayed');
    Route::delete('delete-slip/{slip}', [\App\Http\Controllers\SlipsController::class, 'deleteSlip'])->name('deleteSlip');

    Route::get('/get-competitors', [\App\Http\Controllers\CompetitorsController::class, 'getCompetitors'])->name('getCompetitors');
    Route::get('/get-disciplines', [\App\Http\Controllers\DisciplinesController::class, 'getDisciplines'])->name('getDisciplines');
    Route::get('/get-event-types', [\App\Http\Controllers\EventTypesController::class, 'getEventTypes'])->name('getEventTypes');
    Route::get('/get-selections', [\App\Http\Controllers\SelectionsController::class, 'getSelections'])->name('getSelections');
    Route::get('/get-stats', [\App\Http\Controllers\StatsController::class, 'getStats'])->name('getStats');

    Route::group(['prefix' => 'addRecord'], function () {
        Route::post('discipline', [\App\Http\Controllers\DisciplinesController::class, 'storeDiscipline'])->name('storeDiscipline');
        Route::post('competitor', [\App\Http\Controllers\CompetitorsController::class, 'storeCompetitor'])->name('storeCompetitor');
        Route::post('eventType', [\App\Http\Controllers\EventTypesController::class, 'storeEventType'])->name('storeEventType');
        Route::post('selection/{eventType}', [\App\Http\Controllers\SelectionsController::class, 'storeSelection'])->name('storeSelection');
    });
});

require __DIR__.'/settings.php';
