<?php

    namespace Nila\Permissions;


    use Illuminate\Support\Collection;
    use Nila\Permissions\Contracts\PermissionsContract;
    use Nila\Permissions\Models\Permission;
    use Nila\Permissions\Models\Role;

    class PermissionsService implements PermissionsContract {
        public function findRole( Role|string|int $role ): Role {
            return $this->resolveRole( $role );
        }

        public function findAllRoles(): Collection {
            return Role::all();
        }

        public function findAnyRoles( Role|string|int ...$roles ): Collection {
            $collection = collect();
            foreach ( $roles as $role ) :
                try {
                    $resolved = $this->findRole( $role );
                } catch ( \Throwable $e ) {
                    continue;
                }
                $collection->push( $resolved );
            endforeach;

            return $collection;
        }

        public function findPermission( Permission|int|string $permission ): Permission {
            return $this->resolvePermission( $permission );
        }

        public function findAllPermissions(): Collection {
            return Permission::all();
        }

        public function findAnyPermissions( Permission|string|int ...$permissions ): Collection {
            $collection = collect();
            foreach ( $permissions as $permission ) :
                try {
                    $resolved = $this->findPermission( $permission );
                } catch ( \Throwable $e ) {
                    continue;
                }
                $collection->push( $resolved );
            endforeach;

            return $collection;
        }

        private function resolveRole( Role|string|int $role ): Role {
            return match ( true ) {
                $role instanceof Role => Role::findById( $role->id ),
                is_string( $role ) => Role::findByName( $role ),
                is_int( $role ) => Role::findById( $role )
            };
        }

        private function resolvePermission( Permission|string|int $permission ): Permission {
            return match ( true ) {
                $permission instanceof Permission => Permission::findById( $permission->id ),
                is_string( $permission ) => Permission::findByName( $permission ),
                is_int( $permission ) => Permission::findById( $permission )
            };
        }
    }
