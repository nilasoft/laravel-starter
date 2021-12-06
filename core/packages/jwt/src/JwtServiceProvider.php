<?php


    namespace Nila\Jwt;


    use Nila\Jwt\Contracts\JwtContract;
    use Nila\Jwt\Drivers\JwtUserProvider;
    use Nila\Jwt\Exceptions\JwtErrorCode;
    use Nila\Jwt\Exceptions\JwtException;
    use Nila\Jwt\Guards\JwtGuard;
    use Illuminate\Foundation\Application;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\ServiceProvider;

    class JwtServiceProvider extends ServiceProvider {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register() {
            Auth::provider( 'nilaProvider', function() {
                return new JwtUserProvider;
            } );

            Auth::extend( 'nilaJwt', function( Application $app, $name, array $config ) {
                return $app->makeWith( JwtGuard::class,
                    [ 'provider' => Auth::createUserProvider( $config[ 'provider' ] ) ] );
            } );

            $this->app->singleton( JwtContract::class, function() {
                return new JwtService();
            } );
        }

        /**
         * Bootstrap any application services.
         *
         * @return void
         * @throws JwtException
         */
        public function boot() {
            if ( ! config( 'jwt' ) ) {
                throw new JwtException( 'Please publish the config file!', JwtErrorCode::CONFIG_FILE_NOT_PUBLISHED );
            }
            $this->publishes( [
                __DIR__ . '/../config/config.php' => config_path( 'jwt.php' )
            ], 'jwt-config' );
            $this->mergeConfigFrom( __DIR__ . '/../config/config.php', 'jwt' );
            $this->loadMigrationsFrom( __DIR__ . '/../migrations' );
        }
    }
