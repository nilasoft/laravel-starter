<?php


    namespace App\Http\Controllers\Api\V1;


    use Illuminate\Routing\Controller;
    use Illuminate\Support\Facades\App;
    use Illuminate\Support\Facades\Storage;
    use Nila\Resources\Contracts\SignatureContract;
    use Nila\Resources\Exceptions\ResourcesErrorCode;
    use Nila\Resources\Exceptions\ResourcesException;
    use Nila\Resources\Models\Resource as ResourceModel;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    class ResourceController extends Controller {

        /**
         * Serve the file if request is valid
         *
         * @throws ResourcesException
         */
        public function download( ResourceModel $resource, string $hash ) {
            if ( ! request()->hasValidSignature() ) {
                throw new ResourcesException( 'Your link in not valid!', ResourcesErrorCode::LINK_IS_INVALID,
                    ResponseAlias::HTTP_BAD_REQUEST );
            }
            if ( App::make( SignatureContract::class )->isNotValid( $hash ) ) {
                throw new ResourcesException( 'You\'re not allow to download this file!',
                    ResourcesErrorCode::NOT_ALLOWED_TO_DOWNLOAD, ResponseAlias::HTTP_UNAUTHORIZED );
            }

            return Storage::disk( 'resources' )
                          ->download( $resource->address, $resource->title . $resource->extension );
        }
    }
