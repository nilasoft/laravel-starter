<?php

    namespace Nila\Permissions;

    use Illuminate\Support\Collection;
    use Nila\Permissions\Models\Contracts\Role;
    use Nila\Permissions\Models\Permission;

    trait HasPermissions {
        /**
         * @return string
         */
        private function getDefaultAreaName(): string {
            return config( 'permissions.defaults.area' );
        }

        /**
         * @return string
         */
        private function getArea(): string {
            if ( $this instanceof Role or $this instanceof Permission ) {
                return $this->area;
            }
            if ( $area = $this?->roles()?->first()?->area ) {
                return $area;
            }
            if ( $area = $this?->permissions()?->first()?->area ) {
                return $area;
            }

            return $this->getDefaultAreaName();
        }

        /**
         * @param Permission|string|int $permission
         *
         * @return bool
         */
        public function hasPermissionTo( Permission|string|int $permission ): bool {
            try {
                $resolved = $this->resolvePermission( $permission );
            } catch ( \Throwable $e ) {
                return false;
            }

            return $this->getAllPermissions()->contains( 'id', $resolved->id );
        }

        /**
         * @param Permission|string|int ...$permissions
         *
         * @return bool
         */
        public function hasAnyPermission( Permission|string|int ...$permissions ): bool {
            foreach ( $permissions as $permission ) {
                if ( $this->hasPermissionTo( $permission ) ) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param Permission|string|int ...$permissions
         *
         * @return bool
         */
        public function hasAllPermissions( Permission|string|int ...$permissions ): bool {
            foreach ( $permissions as $permission ) {
                if ( ! $this->hasPermissionTo( $permission ) ) {
                    return false;
                }
            }

            return true;
        }

        /**
         * @param Permission|string|int $permission
         *
         * @return bool
         */
        public function hasDirectPermission( Permission|string|int $permission ): bool {
            if ( $this->hasPermissionTo( $permission ) ) {
                return true;
            }

            return false;
        }

        /**
         * @param Permission|string|int ...$permissions
         *
         * @return bool
         */
        public function hasAnyDirectPermission( Permission|string|int ...$permissions ): bool {
            foreach ( $permissions as $permission ) {
                if ( $this->hasPermissionTo( $permission ) ) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param Permission|string|int ...$permissions
         *
         * @return bool
         */
        public function hasAllDirectPermissions( Permission|string|int ...$permissions ): bool {
            foreach ( $permissions as $permission ) {
                if ( ! $this->hasPermissionTo( $permission ) ) {
                    return false;
                }
            }

            return true;
        }


        /**
         * @return Collection
         */
        public function getPermissionsViaRoles(): Collection {
            $this->loadMissing( 'roles', 'roles.permissions' );

            return $this->getRole()->permissions ?? collect();
        }

        public function getDirectPermissions(): Collection {
            $this->loadMissing( 'permissions' );

            return $this->permissions;
        }

        /**
         * @return Collection
         */
        public function getAllPermissions(): Collection {
            $allPermissions = collect( $this->getDirectPermissions() );

            return $allPermissions->merge( $this->getPermissionsViaRoles() );
        }

        /**
         * @param Permission|Collection|string|int ...$permissions
         *
         */
        public function givePermissionTo( Permission|Collection|string|int ...$permissions ): void {
            $collection = collect();
            foreach ( $permissions as $permission ) {
                $permission = $this->resolvePermission( $permission );
                if ( $permission->area == $this->getArea() ) {
                    $collection->push( $permission->id );
                }
            }
            $result = $this->permissions()->syncWithoutDetaching( $collection->toArray() );
            if ( count( $result[ 'attached' ] ) or count( $result[ 'detached' ] ) or count( $result[ 'updated' ] ) ) {
                $this->load( 'permissions' );
            }
        }

        /**
         * @param Permission|string|int ...$permissions
         *
         */
        public function syncPermissions( Permission|string|int ...$permissions ): void {
            $collection = collect();
            foreach ( $permissions as $permission ) {
                $permission = $this->resolvePermission( $permission );
                if ( $permission->area == $this->getArea() ) {
                    $collection->push( $permission->id );
                }
            }
            $result = $this->permissions()->sync( $collection->toArray() );
            if ( count( $result[ 'attached' ] ) or count( $result[ 'detached' ] ) or count( $result[ 'updated' ] ) ) {
                $this->load( 'permissions' );
            }
        }

        /**
         * @param Permission|string|int $permission
         *
         */
        public function revokePermissionTo( Permission|string|int $permission ): void {
            if ( $this->permissions()->detach( $this->resolvePermission( $permission )->id ) ) {
                $this->load( 'permissions' );
            }
        }

        /**
         * @return Collection
         */
        public function getPermissionNames(): Collection {
            return $this->getDirectPermissions()->pluck( 'name' );
        }


        private function resolvePermission( Permission|string|int $permission, bool $force = false ): Permission {
            $area = $force ? null : $this->getArea();

            return match ( true ) {
                $permission instanceof Permission => Permission::findById( $permission->id, $area ),
                is_string( $permission ) => Permission::findByName( $permission, $area ),
                is_int( $permission ) => Permission::findById( $permission, $area )
            };
        }
    }
