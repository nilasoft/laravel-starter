<?php

    use App\Http\Controllers\Auth\LoginController;
    use Illuminate\Support\Facades\Route;

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

    Route::prefix( 'auth' )->middleware( 'throttle:6,1' )->group( function() {
        Route::get( '/google/callback', [ LoginController::class, 'googleCallback' ] )->name( 'login.google.callback' );
    } );

    Route::middleware( 'web' )->group( function() {
        require __DIR__ . '/authentication/web.php';
    } );
