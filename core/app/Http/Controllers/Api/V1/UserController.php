<?php

    namespace App\Http\Controllers\Api\V1;

    use App\Http\Controllers\Controller;
    use App\JsonApi\V1\Users\UserQuery;
    use App\JsonApi\V1\Users\UserSchema;
    use App\Models\User;
    use Illuminate\Auth\Access\AuthorizationException;
    use LaravelJsonApi\Core\Responses\DataResponse;
    use LaravelJsonApi\Laravel\Http\Controllers\Actions;

    class UserController extends Controller {
        use Actions\FetchMany;
        use Actions\FetchOne;
        use Actions\Store;
        use Actions\Update;
        use Actions\Destroy;
        use Actions\FetchRelated;
        use Actions\FetchRelationship;
        use Actions\UpdateRelationship;
        use Actions\AttachRelationship;
        use Actions\DetachRelationship;

        /**
         * @param UserSchema $schema
         * @param UserQuery  $query
         * @param User       $user
         *
         * @return DataResponse
         * @throws AuthorizationException
         */
//        public function uploads( UserSchema $schema, UserQuery $query, User $user ): DataResponse {
//            $this->authorize( 'uploads', User::class );
//
//            return new DataResponse( $user->uploads );
//        }
    }
