<?php


    namespace Nila\Menus\Http\Resources;


    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\ResourceCollection;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Cache;
    use MenusCacheEnum;
    use Nila\Menus\Exceptions\MenusErrorCode;
    use Nila\Menus\Exceptions\MenusException;
    use Nila\Menus\Models\Menu;
    use Symfony\Component\HttpFoundation\Response;

    class MenusCollection extends ResourceCollection {

        /**
         * Transform the resource collection into an array.
         *
         * @param Request $request
         *
         * @return array
         */
        public function toArray( $request ) {
            return $this->collection->map( function( Menu $item ) {
                // retrieving permissions from database if cache did not exist
                $permissions = app()->runningUnitTests() ? $item->getPermissionNames()
                                                                ->toArray() : Cache::rememberForever( MenusCacheEnum::PERMISSIONS . $item->id,
                    function() use ( $item ) {
                        return $item->getPermissionNames()->toArray();
                    } );
                if ( $permissions ) {
                    if ( ! Auth::check() ) {
                        throw new MenusException( 'Unauthenticated!', MenusErrorCode::USER_UNAUTHENTICATED,
                            Response::HTTP_FORBIDDEN );
                    }
                    if ( ! Auth::user()->canAny( $permissions ) ) {
                        return null;
                    }
                }
                if ( ! is_null( $item->class ) ) {
                    $data[ 'class' ] = $item->class;
                }

                if ( $item->children->isNotEmpty() ) {
                    $data[ 'children' ] = new self( $item->children );
                }

                return array_merge( [
                    'key'   => $item->key,
                    'order' => $item->order,
                    'title' => $item->title,
                    'icon'  => $item->icon,
                    'link'  => route( $item->link ),
                ], $data ?? [] );
            } )->filter( fn( $item ) => $item != null )->toArray();
        }
    }
