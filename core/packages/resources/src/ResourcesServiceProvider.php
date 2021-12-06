<?php


    namespace Nila\Resources;


    use Illuminate\Support\Facades\App;
    use Illuminate\Support\Facades\Route;
    use Nila\Resources\Contracts\ResourcesContract;
    use Nila\Resources\Contracts\SignatureContract;
    use Illuminate\Support\ServiceProvider;

    class ResourcesServiceProvider extends ServiceProvider {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register() {
            $this->app->singleton( SignatureContract::class, function() {
                return new SignatureService( config( 'resources.secret' ) );
            } );

            $this->app->singleton( ResourcesContract::class, function() {
                return new ResourcesService();
            } );

            // register FFMpeg
            $this->app->register( 'ProtoneMedia\LaravelFFMpeg\Support\ServiceProvider' );
            $this->app->alias( 'FFMpeg', 'ProtoneMedia\LaravelFFMpeg\Support\FFMpeg' );
            // register FFMpeg
            $this->app->register( 'Spatie\LaravelImageOptimizer\ImageOptimizerServiceProvider' );
            $this->app->alias( 'ImageOptimizer', 'Spatie\LaravelImageOptimizer\Facades\ImageOptimizer' );
        }

        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot() {
            $this->mergeConfigFrom( __DIR__ . '/../config/config.php', 'resource-config' );
            $this->publishes( [
                __DIR__ . '/../config/config.php' => config_path( 'resources.php' )
            ], 'resource-config' );
            $this->loadMigrationsFrom( __DIR__ . '/../migrations' );

            config( [
                'filesystems.disks' => array_merge( config( 'filesystems.disks' ), [
                    'resources' => [
                        'driver' => 'local',
                        'root'   => config( 'resources.base' ),
                    ]
                ] )
            ] );

            config( [
                'filesystems.links' => array_merge( config( 'filesystems.links' ), [
                    public_path( 'resources' ) => config( 'resources.base' )
                ] )
            ] );

            if ( $this->app->runningUnitTests() ) {
                $this->defineRoutes();
            }
        }

        /**
         * Define routes setup.
         *
         * @return void
         */
        protected function defineRoutes() {
            Route::post( '/upload/{field}', function( string $field ) {
                return response()->json( App::make( ResourcesContract::class )->upload( $field )->getModel(), 201 );
            } )->name( 'resources.test.upload' );

            Route::post( '/external/{field}', function( string $field ) {
                return response()->json( App::make( ResourcesContract::class )->external( $field )->getModel(), 201 );
            } )->name( 'resources.test.external' );

            Route::post( '/batch/{field}', function( string $field ) {
                return response()->json( App::make( ResourcesContract::class )->batch( $field )->getModels(), 201 );
            } )->name( 'resources.test.batch' );
        }

    }
