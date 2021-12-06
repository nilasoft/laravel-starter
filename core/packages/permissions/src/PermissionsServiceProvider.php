<?php

    namespace Nila\Permissions;

    use Illuminate\Support\ServiceProvider;
    use Nila\Permissions\Contracts\PermissionsContract;

    class PermissionsServiceProvider extends ServiceProvider {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register() {
            $this->app->singleton( PermissionsSeeder::class, function() {
                return new PermissionsSeeder();
            } );
            $this->app->singleton( PermissionsContract::class, function() {
                return new PermissionsService();
            } );
        }

        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot() {
            $this->publishes( [
                __DIR__ . '/../config/config.php' => config_path( 'xpermissions.php' ),
            ], 'xpermissions-config' );
            $this->mergeConfigFrom( __DIR__ . '/../config/config.php', 'xpermissions' );
            $this->loadMigrationsFrom( __DIR__ . '/../migrations' );
        }

    }
