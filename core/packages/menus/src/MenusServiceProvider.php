<?php


    namespace Nila\Menus;


    use Nila\Menus\Console\InstallCommand;
    use Illuminate\Support\ServiceProvider;
    use Nila\Menus\Contracts\MenusContract;

    class MenusServiceProvider extends ServiceProvider {

        /**
         * Register any application services.
         *
         * @return void
         */
        public function register() {
            $this->app->singleton( MenusContract::class, function() {
                return new MenusService;
            } );
        }

        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot() {
            $this->loadMigrationsFrom( [
                __DIR__ . '/../migrations',
            ] );

            $this->publishes( [
                __DIR__ . '/../config/config.php' => config_path( 'menus.php' )
            ], 'menus-config' );

            $this->mergeConfigFrom( __DIR__ . '/../config/config.php', 'menus' );

            $this->commands( [ InstallCommand::class ] );
        }
    }
