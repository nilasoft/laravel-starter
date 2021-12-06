<?php

    namespace App\Http\Controllers\Api\V1;

    use App\Http\Controllers\Controller;
    use Hans\XPermissions\Models\Permission;
    use LaravelJsonApi\Core\Responses\DataResponse;
    use LaravelJsonApi\Laravel\Http\Controllers\Actions;

    class RoleController extends Controller {
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

//        public function areas() {
//            return response()->json( [
//                'data' => \GuardsEnum::toArray()
//            ] );
//        }
    }
