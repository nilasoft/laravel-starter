<?php


	namespace Nila\Resources\Traits;


	use Illuminate\Support\Facades\Storage;
	use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

	trait FFMpegPreConfig {
		public function ffmpeg() {
			return FFMpeg::fromDisk( Storage::disk( 'resources' ) )->open( $this->address );
		}
	}
