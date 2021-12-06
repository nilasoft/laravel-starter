<?php

    namespace App\Http\Controllers\Api\V1;

    use App\Http\Controllers\Controller;
    use LaravelJsonApi\Laravel\Http\Controllers\Actions;

    class PermissionController extends Controller {
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
    }
