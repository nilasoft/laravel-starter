<?php

    use App\Http\Controllers\Auth\ForgotPasswordController;
    use App\Http\Controllers\Auth\LoginController;
    use App\Http\Controllers\Auth\RefreshTokenController;
    use App\Http\Controllers\Auth\RegisterController;
    use App\Http\Controllers\Auth\ResetPasswordController;
    use App\Http\Controllers\Auth\VerificationController;

    Route::name( 'api.' )->group( function() {
        // guest routes
        Route::middleware( 'guest' )->group( function() {
            // Login Routes...
            Route::post( 'login', [ LoginController::class, 'login' ] )->name( 'login' );

            // Registration Routes...
            Route::post( 'register', [ RegisterController::class, 'register' ] )->name( 'register' );

            Route::post( 'refresh/token', RefreshTokenController::class )->name( 'refresh.token' );
            // Password Reset Routes...
            Route::post( 'forgot/password', [ ForgotPasswordController::class, 'sendResetLinkEmail' ] )
                 ->name( 'forgot.password' );
            Route::post( 'password/reset', [ ResetPasswordController::class, 'reset' ] )->name( 'password.update' );
        } );

        // Logout Routes...
        Route::post( 'logout', [ LoginController::class, 'logout' ] )->name( 'logout' );

        // authenticated routes
        Route::middleware( 'auth:api' )->group( function() {
            // Email Verification Routes...
            Route::middleware( 'throttle:6,1' )->group( function() {
                Route::post( 'email/resend', [ VerificationController::class, 'resend' ] )
                     ->name( 'verification.resend' );

                Route::post( 'email/verify/{id}/{hash}', [ VerificationController::class, 'verify' ] )
                     ->name( 'verification.verify' );
                     //->middleware( 'signed' );
            } );
        } );
    } );

