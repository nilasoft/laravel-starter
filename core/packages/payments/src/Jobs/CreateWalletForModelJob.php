<?php

    namespace Nila\Payments\Jobs;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldBeUnique;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Arr;

    class CreateWalletForModelJob implements ShouldQueue {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        private Model $model;
        private array $configuration;

        /**
         * Create a new job instance.
         *
         * @return void
         */
        public function __construct( Model $model ) {
            $this->model         = $model;
            $this->configuration = config( 'payments' );
        }

        /**
         * Execute the job.
         *
         * @return void
         */
        public function handle() {
            if ( method_exists( $this->model, 'wallet' ) ) {
                $this->model->wallet()->create( [
                    'currency' => $this->getConfig( 'currency', 'Rial' )
                ] );
            }
        }

        private function getConfig( string $key, $default = null ) {
            return Arr::get( $this->configuration, $key, $default );
        }
    }
