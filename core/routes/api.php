<?php

    use App\Http\Controllers\Api\V1\CallbackController;
    use App\Http\Controllers\Api\V1\MenusController;
    use App\Http\Controllers\Api\V1\PermissionController;
    use App\Http\Controllers\Api\V1\PostController;
    use App\Http\Controllers\Api\V1\ResourceController;
    use App\Http\Controllers\Api\V1\RoleController;
    use App\Http\Controllers\Api\V1\UserController;
    use App\Http\Controllers\Auth\LoginController;
    use Illuminate\Support\Facades\Route;
    use LaravelJsonApi\Laravel\Routing\ActionRegistrar;
    use LaravelJsonApi\Laravel\Routing\Relationships;
    use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
    */

    JsonApiRoute::server( 'v1' )
                ->prefix( 'v1' )
                ->middleware( 'auth:api' )
                ->resources( function( ResourceRegistrar $server ) {
                    $server->resource( 'users', UserController::class )
                           ->relationships( function( Relationships $relationships ) {
                               $relationships->hasMany( 'roles' );
                               $relationships->hasMany( 'posts' );
                           } )
                           ->actions( '-actions', function( ActionRegistrar $action ) {
                               $action->withId()->get( 'uploads' );
                           } );

                    $server->resource( 'roles', RoleController::class )
                           ->relationships( function( Relationships $relationships ) {
                               $relationships->hasMany( 'users' );
                               $relationships->hasMany( 'permissions' );
                           } )
                           ->actions( '-actions', function( ActionRegistrar $action ) {
                               $action->get( 'permissions' )->name('v1.roles.action.permissions');
                               $action->get( 'areas' );
                           } );

                    $server->resource( 'permissions', PermissionController::class )
                           ->relationships( function( Relationships $relationships ) {
                               $relationships->hasMany( 'roles' );
                           } );

                    $server->resource( 'posts', PostController::class )
                           ->relationships( function( Relationships $relationships ) {
                               $relationships->hasOne( 'owner' );
                           } );

                } );

    Route::get( '/login/using/google', [ LoginController::class, 'google' ] )->name( 'login.google' );

    require __DIR__ . '/authentication/api.php';

    Route::get( config( 'resources.link' ), [ ResourceController::class, 'download' ] )->name( 'resources.download' );

    Route::post( config( 'menus.path' ), MenusController::class )->middleware( 'auth:api' )->name( 'menus.api' );

    Route::get( config( 'payments.callback' ), CallbackController::class )->name( 'payments.api.callback' );

    if ( app()->runningUnitTests() ) {
        Route::get( '/me', function() {
            return auth()->user();
        } )->middleware( 'auth:api' )->name( 'test.me' );
    }

