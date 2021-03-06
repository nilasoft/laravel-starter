<?php


    namespace Nila\Resources\Jobs;

    use Nila\Resources\Models\Resource as ResourceModel;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Spatie\LaravelImageOptimizer\OptimizerChainFactory;

    class OptimizePictureJob implements ShouldQueue {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        private ResourceModel $model;
        private Filesystem $storage;

        /**
         * Create a new job instance.
         *
         * @return void
         */
        public function __construct( ResourceModel $model, Filesystem $storage ) {
            $this->model   = $model;
            $this->storage = $storage;
        }

        /**
         * Execute the job.
         *
         * @return void
         */
        public function handle() {
            if ( config( 'resource.optimization.images' ) ) {
                $settings = require __DIR__ . '/../../config/image-optimizer.php';
                OptimizerChainFactory::create( $settings )->optimize( $this->storage->path( $this->model->address ) );
                $this->model->setOptions( [ 'size' => $this->storage->size( $this->model->address ) ] );
            }
            $this->model->update( [ 'published_at' => now() ] );
        }
    }
