<?php


	namespace Nila\Resources\Jobs;

	use FFMpeg\Format\Video\X264;
	use Nila\Resources\Models\Resource as ResourceModel;
	use Illuminate\Bus\Queueable;
	use Illuminate\Contracts\Filesystem\Filesystem;
	use Illuminate\Contracts\Queue\ShouldBeUnique;
	use Illuminate\Contracts\Queue\ShouldQueue;
	use Illuminate\Foundation\Bus\Dispatchable;
	use Illuminate\Queue\InteractsWithQueue;
	use Illuminate\Queue\SerializesModels;

	class OptimizeVideoJob implements ShouldQueue {
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
			if ( config( 'resource.optimization.videos' ) ) {
				// optimization
				$oldFile = $this->model->address;
				$this->model->ffmpeg()
				            ->export()
				            ->inFormat( new X264 )
				            ->save( $this->model->path . '/' . $newFile = \ResourceManagement::generateName() . '.' . $this->model->extension );
				// update file
				$this->model->update( [
					'file' => $newFile
				] );
				// update file's size
				$this->model->setOptions( [ 'size' => $this->storage->size( $this->model->address ) ] );
				// delete old file
				\ResourceManagement::deleteFile( $oldFile );
			}
		}
	}
