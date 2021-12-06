<?php


    namespace Nila\Permissions;


    use Nila\Permissions\Traits\Utilities;
    use Nila\Permissions\Models\Permission;
    use Nila\Permissions\Models\Role;
    use Illuminate\Support\Facades\DB;

    class PermissionsSeeder {
        use Utilities;

        /**
         * create roles
         *
         * @param array  $data
         * @param string $area
         *
         * @throws \Throwable
         */
        public function createRoles( array $data, string $area ) {
            foreach ( $data as $datum ) {
                $payload[] = [
                    'name' => $datum,
                    'area' => $area
                ];
            }
            try {
                DB::beginTransaction();
                Role::insert( $payload );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                dd( 'creating roles failed!', $e->getMessage() );
            }
        }

        /**
         * create permissions for registered models
         *
         * @param array  $data
         * @param string $area
         *
         * @throws \Throwable
         */
        public function createPermissions( array $data, string $area ) {
            $permissionCollection = [];
            foreach ( $data as $key => $datum ) {
                // get model normalized name
                $model = $this->getModel( $key, $datum );
                // create permissions
                $permissionCollection = array_merge( $permissionCollection,
                    $this->generatePermissions( $model, $datum, $area ) );
            }
            try {
                DB::beginTransaction();
                // store permissions
                Permission::insert( $permissionCollection );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                dd( 'creating permissions failed!', $e->getMessage() );
            }
        }

        public function createSuperPermissions( array $data, string $area ): void {
            $permissionCollection = collect();
            foreach ( $data as $key => $datum ) {
                if ( is_string( $key ) ) {
                    $permission = $this->normalizeModelName( $key ) . $this->prefix . $datum;
                } else {
                    $permission = $datum . $this->prefix . $datum;
                }
                $permissionCollection->push( [
                    'name' => $permission,
                    'area' => $area
                ] );
            }
            try {
                DB::beginTransaction();
                Permission::insert( $permissionCollection->toArray() );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                dd( 'creating super permissions failed!', $e->getMessage() );
            }
        }

        /**
         * assign permissions to the given role
         *
         * @param Role   $role
         * @param array  $data
         * @param string $area
         *
         * @throws \Throwable
         */
        public function assignPermissionsToRole( Role $role, array $data, string $area ) {
            try {
                DB::beginTransaction();
                foreach ( $data as $key => $datum ) {
                    $model = $this->getModel( $key, $datum );
                    if ( is_array( $datum ) ) {
                        foreach ( $datum as $item ) {
                            $permission = $item == '*' ? $this->generatePermissions( $model, null,
                                $area ) : $this->generatePermission( $model, $item, $area );
                            $role->givePermissionTo( ... collect( $permission )->pluck( 'name' ) );
                        }
                    } elseif ( is_string( $datum ) ) {
                        $permission = $datum == '*' ? $this->generatePermissions( $model, null,
                            $area ) : $this->generatePermission( $model, $datum, $area );
                        $role->givePermissionTo( ... collect( $permission )->pluck( 'name' ) );
                    }
                }
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                dd( 'assigning permissions to role failed!', $e->getMessage() );
            }
        }

        public function assignSuperPermissionsToRole( Role $role, array $data ) {
            $permissionCollection = collect();
            foreach ( $data as $datum ) {
                if ( $datum != '*' ) {
                    $permission = $this->normalizeModelName( $datum ) . $this->prefix . '*';
                } else {
                    $permission = '*' . $this->prefix . '*';
                }
                $permissionCollection->push( $permission );
            }
            try {
                DB::beginTransaction();
                $role->givePermissionTo( ...$permissionCollection->toArray() );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                dd( 'assigning super permissions to role failed!', $e->getMessage() );
            }
        }
    }
