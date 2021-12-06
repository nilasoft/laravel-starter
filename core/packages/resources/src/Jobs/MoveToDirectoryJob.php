<?php


    namespace Nila\Resources\Jobs;

    use Nila\Resources\Models\Resource as ResourceModel;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Storage;

    class MoveToDirectoryJob implements ShouldQueue {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        private ResourceModel $model;
        private array $configuration, $images, $videos, $files;
        private Filesystem $storage;

        /**
         * Create a new job instance.
         *
         * @return void
         */
        public function __construct( ResourceModel $model ) {
            $this->model         = $model;
            $this->configuration = config( 'resources' );
            $this->images        = $this->getConfig( 'extensions.images' );
            $this->videos        = $this->getConfig( 'extensions.videos' );
            $this->files         = $this->getConfig( 'extensions.files' );
            $this->storage       = Storage::disk( 'resources' );
        }

        private function getConfig( string $key ) {
            return Arr::get( $this->configuration, $key );
        }

        /**
         * Execute the job.
         *
         * @return void
         * @throws \Exception
         */
        public function handle() {
            if ( $this->model->isExternal() ) {
                return;
            }
            if ( $this->getConfig( 'temp' ) ) {
                $this->moveFileAndUpdateModel( $this->makeDirectoryIfNotExists() );
            }
            $this->optimization();
        }

        private function optimization() {
            if ( in_array( $this->model->extension, $this->images ) ) {
                OptimizePictureJob::dispatch( $this->model, $this->storage );
            } else if ( in_array( $this->model->extension, $this->videos ) ) {
                OptimizeVideoJob::withChain( [
                    new GenerateHLSJob( $this->model, $this->storage )
                ] )->dispatch( $this->model, $this->storage );
            } else {
                $this->model->update( [ 'published_at' => now() ] );
            }
        }

        private function makeDirectoryIfNotExists(): string {
            if ( ! $this->storage->exists( $folder = $this->getConfig( 'classification' ) ) ) {
                $this->storage->makeDirectory( $folder );
            }

            return $folder;
        }

        private function moveFileAndUpdateModel( string $folder ): bool {
            if ( $this->storage->move( $this->model->address, $folder . '/' . $this->model->file ) ) {
                return $this->model->update( [
                    'path' => $folder
                ] );
            }

            return false;
        }

    }
