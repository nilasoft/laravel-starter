<?php


    namespace Nila\Resources\Models;


    use Illuminate\Support\Facades\App;
    use Nila\Resources\Contracts\SignatureContract;
    use Nila\Resources\Scopes\PublishedOnlyScope;
    use Nila\Resources\Traits\FFMpegPreConfig;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\URL;
    use Symfony\Component\HttpFoundation\BinaryFileResponse;

    class Resource extends Model {
        use FFMpegPreConfig;

        private array $configuration;
        private Filesystem $storage;
        protected $fillable = [ 'title', 'path', 'file', 'hls', 'extension', 'options', 'external', 'published_at' ];

        protected $casts = [
            'options'  => 'array',
            'external' => 'boolean'
        ];

        public function __construct( array $attributes = [] ) {
            parent::__construct( $attributes );
            $this->configuration = config( 'resources' );
            $this->storage       = Storage::disk( 'resources' );
        }

        protected static function booted() {
            if ( config( 'resources.onlyPublishedFiles' ) ) {
                self::addGlobalScope( new PublishedOnlyScope() );
            }
        }

        public function getUrlAttribute() {
            return $this->isExternal() ? $this->path : URL::temporarySignedRoute( 'resources.download',
                now()->addMinutes( $this->getConfig( 'expiration' ) ), [
                    'resource' => $this->id,
                    'hash'     => App::make(SignatureContract::class)->create()
                ] );
        }

        public function getHlsUrlAttribute() {
            if ( ! $this->isPublished() ) {
                return null;
            }
            if ( in_array( $this->extension, $this->getConfig( 'extensions.audio' ) ) ) {
                $response = new BinaryFileResponse( $this->storage->path( $this->address ) );
                BinaryFileResponse::trustXSendfileTypeHeader();

                return $response;
            }
            if ( $this->getConfig( 'hls.enable' ) ) {
                return url( 'resources/' . $this->path . '/' . $this->hls );
            } else {
                return url( 'resources/' . $this->path . '/' . $this->file );
            }
        }

        public function getAddressAttribute() {
            return $this->path . '/' . $this->file;
        }

        public function isExternal(): bool {
            return $this->external;
        }

        public function isPublished(): bool {
            return $this->published_at != null;
        }

        public function status(): string {
            return $this->isPublished() ? 'published' : 'waiting';
        }

        public function setOptions( array $options ) {
            return $this->update( [ 'options' => array_merge( $this->getOptions(), $options ) ] );
        }

        public function getOptions() {
            return $this->options;
        }

        private function getConfig( string $key ) {
            return Arr::get( $this->configuration, $key );
        }
    }
