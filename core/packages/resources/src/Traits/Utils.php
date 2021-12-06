<?php


    namespace Nila\Resources\Traits;


    use Nila\Resources\Exceptions\ResourcesErrorCode;
    use Nila\Resources\Exceptions\ResourcesException;
    use Nila\Resources\Models\Resource as ResourceModel;
    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\ValidationException;
    use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    trait Utils {
        /**
         * Get the package configurations
         *
         * @throws ResourcesException if configuration file didn't publish
         */
        private function getConfig( string $name ) {
            if ( $config = config( $name ) ) {
                return $config;
            }

            throw new ResourcesException( 'please publish the Resources config file!',
                ResourcesErrorCode::CONFIG_NOT_PUBLISHED );
        }

        /**
         * Validate the coming request
         *
         * @throws ValidationException|ResourcesException if validation is failed
         */
        private function validate( string $field, array $additional = [], string $type = null ): array {
            $validator = Validator::make( $this->getRequest( $field ), [
                $field => array_merge( $this->configuration[ 'validation' ][ $type ?? $this->getFileType( $field ) ] ?? [],
                    $additional, [ 'bail' ] ),
            ] )->after( function( \Illuminate\Validation\Validator $validator ) use ( $field ) {
                if ( $validator->errors()->isNotEmpty() or request()->file( $field ) instanceof UploadedFile ) {
                    return;
                }

                if ( ! in_array( $this->getExtension( $field ), $extensions = $this->getAllowedExtensions( false ) ) ) {
                    $validator->errors()
                              ->add( $field, 'The link must be a file of type: ' . implode( ', ', $extensions ) );
                }
            } );

            return $validator->validate();
        }

        /**
         * Get all file and input request and merge them into one array
         *
         * @param string $field
         *
         * @return array
         */
        private function getRequest( string $field ): array {
            if ( ! empty( $this->request ) ) {
                return $this->request;
            }
            $files  = null;
            $inputs = null;
            if ( request()->hasFile( $field ) ) {
                $files = request()->file( $field );
            }
            if ( request()->hasAny( $field ) ) {
                $inputs = request()->input( $field );
            }
            $data = [ $field => $files ?? $inputs ];

            if ( is_array( $files ) ) {
                $data = [ $field => array_merge( $files, $inputs ?? [] ) ];
            }

            return $data ?? [];
        }

        /**
         * Get a specific field from request
         *
         * @param string $field
         *
         * @return mixed
         */
        private function getFromRequest( string $field ): mixed {
            return Arr::get( $this->getRequest( $field ), $field );
        }

        /**
         * Get maximum size defined for a specific file type
         *
         * @param string $field
         *
         * @return string
         * @throws ResourcesException
         */
        private function getMaxSize( string $field ): string {
            return Arr::get( $this->configuration, 'sizes.' . $this->getFileType( $field ), 1 ) * 1024;
        }

        /**
         * Determine the file type by the file's extension
         *
         * @param string $field
         *
         * @return string
         * @throws ResourcesException
         */
        private function getFileType( string $field ): string {
            $extension = $this->getExtension( $field );
            // TODO: needs to refactor
            if ( in_array( $extension, $this->configuration[ 'extensions' ][ 'images' ] ) ) {
                return 'image';
            } elseif ( in_array( $extension, $this->configuration[ 'extensions' ][ 'videos' ] ) ) {
                return 'video';
            } elseif ( in_array( $extension, $this->configuration[ 'extensions' ][ 'audio' ] ) ) {
                return 'audio';
            } elseif ( in_array( $extension, $this->configuration[ 'extensions' ][ 'files' ] ) ) {
                return 'file';
            }
            // detect file type should be automated
            throw new ResourcesException( 'Unknown file type! the file extension is not in the extensions list.',
                ResourcesErrorCode::UNKNOWN_FILE_TYPE, ResponseAlias::HTTP_BAD_REQUEST );
        }

        /**
         * Get the file's extension based-on request type
         *
         * @param        $field
         * @param string $prefix
         *
         * @return string
         * @throws ResourcesException
         */
        private function getExtension( $field, string $prefix = '' ): string {
            if ( ( $type = $this->getFieldType( $field ) ) == 'file' ) {
                return $prefix . $this->getFileExtension( $field );
            } elseif ( $type == 'link' ) {
                return $prefix . $this->getUrlExtension( $field );
            }

            throw new ResourcesException( 'Unknown Extension!', ResourcesErrorCode::UNKNOWN_EXTENSION,
                ResponseAlias::HTTP_BAD_REQUEST );
        }

        /**
         * Get the uploaded file's extension
         *
         * @param string $field
         *
         * @return string
         */
        private function getFileExtension( string $field ): string {
            return $this->getFromRequest( $field )->extension();
        }

        /**
         * Get the target file's extension from the link
         *
         * @param string $field
         *
         * @return string
         */
        private function getUrlExtension( string $field ): string {
            $file      = Arr::last( explode( '/', $this->getFromRequest( $field ) ) );
            $extension = Arr::last( explode( '.', $file ) );
            if ( str_contains( $extension, '?' ) ) {
                $extension = substr( $extension, 0, strpos( $extension, '?' ) );
            }

            return $extension;
        }

        /**
         * Get a list of allowed extensions
         *
         * @param bool $implode
         *
         * @return array|string
         */
        private function getAllowedExtensions( bool $implode = true ): array|string {
            $data = [];
            foreach ( $this->configuration[ 'extensions' ] as $extension ) {
                $data = array_merge( $data, $extension );
            }

            return $implode ? implode( ',', $data ) : $data;
        }

        /**
         * Store the uploaded file in defined folder
         *
         * @param UploadedFile $file
         *
         * @return string
         */
        private function uploader( UploadedFile $file ): string {
            return $this->storage->putFileAs( $this->model->path, $file, $this->model->file );
        }

        /**
         * Save and return file as a eloquent model
         *
         * @param array $data
         *
         * @return ResourceModel
         */
        private function save( array $data ): ResourceModel {
            return ResourceModel::create( $data );
        }

        /**
         * Get the uploaded file's details
         *
         * @param string $field
         *
         * @return array
         * @throws ResourcesException
         */
        public function getOptions( string $field ): array {
            $data = [
                'size'     => ( $file = $this->getFromRequest( $field ) )->getSize(),
                'mimeType' => $file->getMimeType()
            ];
            if ( ( $type = $this->getFileType( $field ) ) == 'image' ) {
                if ( $dimensions = getimagesize( $file->getRealPath() ) ) {
                    $data[ 'width' ]  = $dimensions[ 0 ];
                    $data[ 'height' ] = $dimensions[ 1 ];
                }
            } elseif ( $type == 'video' ) {
                try {
                    $tempFile = $this->storage->putFile( $this->generateFolder(), $file );
                    FFMpeg::fromFilesystem( $this->storage )
                          ->open( $tempFile )
                          ->getFrameFromSeconds( 1 )
                          ->export()
                          ->save( $tempFrame = $this->generateName() . '.png' );
                    if ( $this->storage->exists( $tempFrame ) ) {
                        if ( $dimensions = getimagesize( $this->storage->path( $tempFrame ) ) ) {
                            $data[ 'width' ]  = $dimensions[ 0 ];
                            $data[ 'height' ] = $dimensions[ 1 ];
                        }
                        $this->storage->delete( $tempFrame );
                    }
                    $data[ 'duration' ] = FFMpeg::fromFilesystem( $this->storage )
                                                ->open( $tempFile )
                                                ->getDurationInSeconds();
                    $this->storage->delete( $tempFile );
                } catch ( \Throwable $e ) {
                    // taking a frame from video failed
                }
            }

            return $data;
        }

        /**
         * Set a title for the file based-on the file name
         *
         * @param string $field
         *
         * @return string
         * @throws ResourcesException
         */
        public function setTitle( string $field ): string {
            $title = explode( '.', $this->getFilename( $field ) );
            $name  = '';
            foreach ( $title as $item ) {
                $name .= $item != end( $title ) ? '-' . $item : '';
            }

            return ltrim( $name, '-' );
        }

        /**
         * Get the file name ( file or link )
         *
         * @param string $field
         *
         * @return string
         * @throws ResourcesException
         */
        private function getFilename( string $field ): string {
            $fieldType = $this->getFieldType( $field );
            switch ( $fieldType ) {
                case 'file' :
                    return $this->getFromRequest( $field )->getClientOriginalName();
                case 'link' :
                    $url      = explode( '/', $this->getFromRequest( $field ) );
                    $filename = end( $url );

                    return str_contains( $filename, '?' ) ? substr( $filename, 0,
                        strpos( $filename, '?' ) ) : $filename;
                default:
                    return $this->generateName() . $this->getExtension( $field, '.' );
            }
        }

        /**
         * Determine that the field's data is what
         *
         * @param string $field
         *
         * @return string
         * @throws ResourcesException
         */
        private function getFieldType( string $field ): string {
            if ( $this->getFromRequest( $field ) instanceof UploadedFile ) {
                return 'file';
            } elseif ( is_string( $this->getFromRequest( $field ) ) ) {
                return 'link';
            }

            throw new ResourcesException( 'Unknown field type! supported field types: file, string',
                ResourcesErrorCode::UNKNOWN_FIELD_TYPE, ResponseAlias::HTTP_BAD_REQUEST );
        }
    }
