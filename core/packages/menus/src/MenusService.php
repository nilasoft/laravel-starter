<?php

    namespace Nila\Menus;

    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Cache;
    use MenusCacheEnum;
    use Nila\Menus\Contracts\MenusContract;
    use Nila\Menus\Exceptions\MenusErrorCode;
    use Nila\Menus\Exceptions\MenusException;
    use Nila\Menus\Models\Menu;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    class MenusService implements MenusContract {

        /**
         * @param string $key
         *
         * @return Collection
         * @throws MenusException
         */
        public function find( string $key ): Collection {
            if ( Menu::keyExists( $key ) ) {
                return Cache::rememberForever( MenusCacheEnum::MENU . $key, function() use ( $key ) {
                    return Menu::generate( $key )->get();
                } );
            }
            throw new MenusException( 'Menu not found!', MenusErrorCode::MENU_NOT_FOUND,
                ResponseAlias::HTTP_NOT_FOUND );
        }

        /**
         * @param string ...$keys
         *
         * @return Collection
         * @throws MenusException
         */
        public function findAny( string ...$keys ): Collection {
            $collection = collect();
            foreach ( $keys as $key ) :
                $collection->push( $this->find( $key ) );
            endforeach;

            return $collection;
        }

        public function findAll(): Collection {
            return Cache::rememberForever( MenusCacheEnum::MENU_ALL, function() {
                return Menu::generate()->get();
            } );
        }
    }
