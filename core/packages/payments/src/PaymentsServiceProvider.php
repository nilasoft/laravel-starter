<?php

    namespace Nila\Payments;

    use Illuminate\Support\ServiceProvider;
    use Nila\Payments\Commands\PendingPaymentsFailureCommand;
    use Nila\Payments\Contracts\PaymentsContract;
    use Nila\Payments\Gateways\Contracts\PaymentsGateway;


    class PaymentsServiceProvider extends ServiceProvider {
        /**
         * Register any application services.
         *
         * @return void
         */

        public function register() {
            $this->app->singleton( PaymentsGateway::class, function() {
                return get_gateway_class( (string) config( 'payments.apis.default' ) );
            } );
            $this->app->singleton( PaymentsContract::class, function() {
                return new PaymentsService;
            } );
        }

        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot() {
            $this->loadMigrationsFrom( __DIR__ . '/../migrations' );
            $this->mergeConfigFrom( __DIR__ . '/../config/config.php', 'payments-config' );
            $this->publishes( [
                __DIR__ . '/../config/config.php' => config_path( 'payments.php' ),
            ], 'payments-config' );
            $this->commands( [ PendingPaymentsFailureCommand::class ] );
        }
    }
