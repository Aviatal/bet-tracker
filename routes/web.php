<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('dashboard', [\App\Http\Controllers\BetsController::class, 'index'])->name('dashboard');
    Route::get('createBet', [\App\Http\Controllers\BetsController::class, 'createBet'])->name('createBet');
    Route::post('storeBet', [\App\Http\Controllers\BetsController::class, 'storeBet'])->name('storeBet');
    Route::patch('change-bet-status/{bet}', [\App\Http\Controllers\BetsController::class, 'changeBetStatus'])->name('changeBetStatus');

    Route::get('/get-competitors', [\App\Http\Controllers\CompetitorsController::class, 'getCompetitors'])->name('getCompetitors');
    Route::get('/get-disciplines', [\App\Http\Controllers\DisciplinesController::class, 'getDisciplines'])->name('getDisciplines');
    Route::get('/get-event-types', [\App\Http\Controllers\EventTypesController::class, 'getEventTypes'])->name('getEventTypes');
    Route::get('/get-selections', [\App\Http\Controllers\SelectionsController::class, 'getSelections'])->name('getSelections');
    Route::get('/get-stats', [\App\Http\Controllers\StatsController::class, 'getStats'])->name('getStats');

    Route::group(['prefix' => 'addRecord'], function () {
        Route::post('discipline', [\App\Http\Controllers\DisciplinesController::class, 'storeDiscipline'])->name('storeDiscipline');
        Route::post('competitor', [\App\Http\Controllers\CompetitorsController::class, 'storeCompetitor'])->name('storeCompetitor');
        Route::post('eventType', [\App\Http\Controllers\EventTypesController::class, 'storeEventType'])->name('storeEventType');
        Route::post('selection', [\App\Http\Controllers\SelectionsController::class, 'storeSelection'])->name('storeSelection');
    });
});

require __DIR__.'/settings.php';
