<?php

    namespace Nila\Jwt\Traits;

    use Illuminate\Support\Facades\App;
    use Nila\Jwt\Contracts\JwtContract;

    trait JwtTokenCan {
        private array $tokenPermissions;

        /**
         * Determine if the entity has the given abilities.
         *
         * @param       $abilities
         * @param array $arguments
         *
         * @return bool
         */
        public function can( $abilities, $arguments = [] ) {
            if ( is_string( $abilities ) or is_int( $abilities ) ) {
                return $this->tokenCan( $abilities );
            }

            if ( is_array( $abilities ) ) {
                foreach ( $abilities as $ability ) {
                    if ( ! $this->can( $ability ) ) {
                        return false;
                    }
                }

                return true;
            }

            return false;
        }

        /**
         * Determine if the entity has any of the given abilities.
         *
         * @param iterable|string $abilities
         * @param array|mixed     $arguments
         *
         * @return bool
         */
        public function canAny( $abilities, $arguments = [] ) {
            if ( is_string( $abilities ) or is_int( $abilities ) ) {
                return $this->can( $abilities );
            }

            if ( is_array( $abilities ) ) {
                foreach ( $abilities as $ability ) {
                    if ( $this->can( $ability ) ) {
                        return true;
                    }
                }

                return false;
            }

            return false;
        }

        /**
         * Determine if the entity does not have the given abilities.
         *
         * @param iterable|string $abilities
         * @param array|mixed     $arguments
         *
         * @return bool
         */
        public function cant( $abilities, $arguments = [] ) {
            return ! $this->can( $abilities, $arguments );
        }

        /**
         * Determine if the entity does not have the given abilities.
         *
         * @param iterable|string $abilities
         * @param array|mixed     $arguments
         *
         * @return bool
         */
        public function cannot( $abilities, $arguments = [] ) {
            return $this->cant( $abilities, $arguments );
        }

        private function tokenCan( string|int $ability ) {
            if ( ! isset( $this->tokenPermissions ) ) {
                $this->tokenPermissions = App::make( JwtContract::class )
                                             ->session()
                                             ->getPermissions( request()->bearerToken() );
            }

            [ $model ] = explode( '-', $ability );
            // high order permissions
            if ( in_array( "*-*", $this->tokenPermissions ) ) {
                return true;
            }
            if ( in_array( "$model-*", $this->tokenPermissions ) ) {
                return true;
            }
            // -----
            if ( is_int( $ability ) ) {
                return in_array( $ability, array_keys( $this->tokenPermissions ) );
            }
            if ( is_string( $ability ) ) {
                return in_array( $ability, array_values( $this->tokenPermissions ) );
            }

            return false;
        }
    }
