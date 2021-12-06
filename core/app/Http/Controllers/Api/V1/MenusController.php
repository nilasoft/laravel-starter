<?php


    namespace App\Http\Controllers\Api\V1;


    use Illuminate\Routing\Controller;
    use Illuminate\Support\Facades\App;
    use Nila\Menus\Contracts\MenusContract;
    use Nila\Menus\Http\Resources\MenusCollection;

    class MenusController extends Controller {

        /**
         * @param string|null $key
         *
         * @return MenusCollection
         */
        public function __invoke( string $key = null ): MenusCollection {
            if ( $key ) {
                $menus = app( MenusContract::class )->find( $key );
            } else {
                $menus = app( MenusContract::class )->findAll();
            }

            return new MenusCollection( $menus );
        }
    }
