<?php


    namespace Nila\Permissions\Traits;


    trait Utilities {
        private string $prefix = '-';
        private array $keys = [ 'viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete' ];

        /**
         * detect model and normalize the model name
         *
         * @param $key
         * @param $datum
         *
         * @return string
         */
        private function getModel( $key, $datum ): string {
            return $this->normalizeModelName( is_string( $key ) ? $key : $datum );
        }

        /**
         * normalize the model name
         *
         * @param string $model
         *
         * @return string
         */
        private function normalizeModelName( string $model ): string {
            return array_reverse( explode( '\\', strtolower( $model ) ) )[ 0 ];
        }

        /**
         * generate permissions with basics
         *
         * @param string $model
         * @param        $datum
         * @param null   $area
         *
         * @return array
         */
        private function generatePermissions( string $model, $datum, $area ): array {
            $permissions = $this->_generateBasicPermissions( $model, $area );
            $additional  = $this->_generateAdditionalPermissions( $model, $datum, $area );

            return array_merge( $permissions, $additional );
        }

        /**
         * generate permission without basics
         *
         * @param string $model
         * @param        $datum
         * @param null   $area
         *
         * @return array
         */
        private function generatePermission( string $model, $datum, $area ): array {
            return $this->_generateAdditionalPermissions( $model, $datum, $area );
        }


        /**
         * generate basics permissions
         *
         * @param string $model
         * @param        $area
         *
         * @return array
         */
        private function _generateBasicPermissions( string $model, $area ): array {
            foreach ( $this->keys as $key ) {
                $permissions[] = [
                    'name' => $model . $this->prefix . $key,
                    'area' => $area
                ];
            }

            return $permissions ?? [];
        }

        /**
         * generate additional permissions
         *
         * @param string $model
         * @param        $datum
         * @param        $area
         *
         * @return array
         */
        private function _generateAdditionalPermissions( string $model, $datum, $area ): array {
            if ( is_array( $datum ) ) {
                foreach ( $datum as $additionalPermission ) {
                    $permissions[] = [
                        'name' => $model . $this->prefix . $additionalPermission,
                        'area' => $area
                    ];
                }
            } else if ( is_string( $datum ) ) {
                $permissions[] = [
                    'name' => $model . $this->prefix . $datum,
                    'area' => $area
                ];
            }

            return $permissions ?? [];
        }
    }
