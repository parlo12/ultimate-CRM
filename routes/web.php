<?php

    use App\Http\Controllers\Customer\PusherController;
    use App\Http\Controllers\Debug\DebugController;
    use App\Http\Controllers\LanguageController;


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

    Route::get('/', function () {

        if (config('app.stage') == 'new') {
            return redirect('install');
        }

        return redirect('login');
    });

// locale Route
    Route::get('lang/{locale}', [LanguageController::class, 'swap']);
    Route::any('languages', [LanguageController::class, 'languages'])->name('languages');

    Route::post('/pusher/auth', [PusherController::class, 'pusherAuth'])
        ->middleware('auth')->name('pusher.auth');

    if (config('app.stage') == 'local') {

        Route::get('debug', [DebugController::class, 'index'])->name('debug');
    }
