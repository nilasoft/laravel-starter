<?php


    namespace Nila\Resources;


    use Nila\Resources\Contracts\ResourcesContract;
    use Nila\Resources\Exceptions\ResourcesErrorCode;
    use Nila\Resources\Exceptions\ResourcesException;
    use Nila\Resources\Jobs\MoveToDirectoryJob;
    use Nila\Resources\Models\Resource as ResourceModel;
    use Nila\Resources\Traits\Utils;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Illuminate\Validation\ValidationException;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    class ResourcesService implements ResourcesContract {
        use Utils;

        private array $configuration;
        private Filesystem $storage;
        private ResourceModel $model;
        private Collection $models;
        private array $request = [];

        /**
         * @throws ResourcesException if config not found
         */
        public function __construct() {
            $this->configuration = $this->getConfig( 'resources' );
            $this->storage       = Storage::disk( 'resources' );
            $this->models        = collect();
        }

        /**
         * Validate the request and upload the file
         *
         * @param string $field
         *
         * @return $this
         * @throws ResourcesException
         * @throws ValidationException|\Throwable
         */
        public function upload( string $field ): self {
            $this->validate( $field, [
                'required',
                'mimes:' . $this->getAllowedExtensions(),
                'max:' . $this->getMaxSize( $field )
            ] );
            try {
                DB::beginTransaction();
                $this->model = $this->save( [
                    'title'     => $this->setTitle( $field ),
                    'path'      => $this->generateFolder() . '/' . $this->generateName( 'string', 8 ),
                    'file'      => $this->generateName() . '.' . $extension = $this->getExtension( $field ),
                    'extension' => $extension,
                    'options'   => $this->getOptions( $field )
                ] );
                $this->uploader( $this->getFromRequest( $field ) );
                DB::commit();
                MoveToDirectoryJob::dispatch( $this->model );
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new ResourcesException( 'Upload failed! ' . $e->getMessage(), ResourcesErrorCode::UPLOAD_FAILED );
            }

            return $this;
        }

        /**
         * Store a given external link
         *
         * @param string $field
         *
         * @return $this
         * @throws ResourcesException
         * @throws ValidationException|\Throwable
         */
        public function external( string $field ): self {
            $this->validate( $field, [ 'required', 'url' ], 'external' );
            try {
                DB::beginTransaction();
                $this->model = $this->save( [
                    'title'        => $this->setTitle( $field ),
                    'path'         => $this->getFromRequest( $field ),
                    'extension'    => $this->getExtension( $field ),
                    'external'     => true,
                    'published_at' => now()
                ] );
                $this->model->refresh();
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new ResourcesException( 'External link store failed!',
                    ResourcesErrorCode::EXTERNAL_LINK_STORE_FAILED );
            }

            return $this;
        }

        /**
         * Store the given files and links
         *
         * @param string $field
         *
         * @return $this
         * @throws ResourcesException
         * @throws ValidationException|\Throwable
         */
        public function batch( string $field ): self {
            $this->request = $this->getRequest( $field );
            if ( $this->request[ $field ] == null ) {
                throw new ResourcesException( 'Empty request! the \'' . $field . '\' key is null.',
                    ResourcesErrorCode::KEY_IS_NULL, ResponseAlias::HTTP_BAD_REQUEST );
            }
            foreach ( $this->request as $items ) {
                foreach ( $items as $key => $item ) {
                    if ( $item instanceof UploadedFile ) {
                        $this->models->push( $this->upload( $field . '.' . $key )->getModel() );
                    } elseif ( is_string( $item ) ) {
                        $this->models->push( $this->external( $field . '.' . $key )->getModel() );
                    }
                }
            }

            return $this;
        }

        /**
         * Delete a specific file
         *
         * @param string $path
         *
         * @return bool
         */
        public function deleteFile( string $path ): bool {
            if ( $this->storage->exists( $path ) ) {
                return $this->storage->delete( $path );
            }

            return false;
        }

        /**
         * Generate a unique name according to the determined driver
         *
         * @param string|null $driver
         * @param int         $length
         *
         * @return string
         */
        public function generateName( string $driver = null, int $length = 16 ): string {
            switch ( $driver ? : $this->configuration[ 'naming' ] ) {
                case 'uuid' :
                    return Str::uuid();
                case 'string':
                    return Str::random( $length );
                case 'digits':
                    return substr( str_shuffle( '012345678901234567890123456789' ), 0, $length );
                case 'string_digits':
                    return substr( str_shuffle( '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' ), 0,
                        $length );
                case 'symbols':
                    return substr( str_shuffle( '!@#$%^&*(){}><?~' ), 0, $length );
                case 'hash':
                    return substr( bcrypt( time() ), 0, $length );
            }

            return Str::uuid();
        }

        /**
         * Create the folder to store the uploaded file
         *
         * @return string
         */
        public function generateFolder(): string {
            $folder = $this->configuration[ 'temp' ] ? : $this->configuration[ 'classification' ];
            if ( ! $this->storage->exists( $folder ) ) {
                $this->storage->makeDirectory( $folder );
            }

            return ltrim( $folder, '/' );
        }

        /**
         * After upload actions, you can get the related model
         *
         * @return array|ResourceModel
         */
        public function getModel(): array|ResourceModel {
            return is_array( $keys = $this->configuration[ 'keys' ] ) ? $this->model->refresh()
                                                                                    ->only( $keys ) : $this->model->refresh();
        }

        /**
         * After batch upload, you can get the related models
         *
         * @return Collection
         */
        public function getModels(): Collection {
            return $this->models;
        }

        /**
         * After upload actions, you can get the related model's id
         *
         * @return int
         */
        public function getId(): int {
            return $this->model->id;
        }

        /**
         * After batch upload, you can get the ids of related models
         *
         * @return array
         */
        public function getIds(): array {
            return $this->models->pluck( 'id' )->flatten()->toArray();
        }

        /**
         * Delete a specific resource include source file, hls etc
         *
         * @param $id
         *
         * @return bool
         */
        public function delete( $id ): bool {
            $model = ResourceModel::findOrFail( $id );
            try {
                if ( $this->storage->exists( $model->path ) ) {
                    $this->storage->deleteDirectory( $model->path );
                }

                $model->delete();
            } catch ( \Throwable $e ) {
                return false;
            }

            return true;
        }

        /**
         * Delete resources in batch mode
         *
         * @param array $ids
         *
         * @return array
         */
        public function batchDelete( array $ids ): array {
            $results = collect();
            foreach ( $ids as $id ) {
                $results->put( $id, $this->delete( $id ) );
            }

            return $results->toArray();
        }
    }
