<?php

    namespace Nila\Permissions;

    use Nila\Permissions\Exceptions\PermissionsErrorCode;
    use Nila\Permissions\Exceptions\PermissionsException;
    use Nila\Permissions\Models\Role;
    use Illuminate\Support\Collection;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    trait HasRoles {
        use HasPermissions;

        public function assignRole( Role|string|int $role, bool $force = false ): void {
            $result = $this->roles()->sync( $this->resolveRole( $role, $force ) );
            if ( count( $result[ 'attached' ] ) or count( $result[ 'detached' ] ) or count( $result[ 'updated' ] ) ) {
                $this->load( 'roles' );
            }
        }

        public function hasRole( Role|string|int $role ): bool {
            if ( $this->getRole()?->id == $this->resolveRole( $role )->id ) {
                return true;
            }

            return false;
        }

        public function hasAnyRole( Role|string|int ...$roles ): bool {
            foreach ( $roles as $role ) {
                try {
                    $resolved = $this->hasRole( $role );
                } catch ( \Throwable $e ) {
                    continue;
                }
                if ( $resolved ) {
                    return true;
                }
            }

            return false;
        }

        public function getRoleName(): string {
            return $this->getRole()->name;
        }

        private function resolveRole( Role|string|int $role, bool $force = false ): Role {
            $area = $force ? null : $this->getArea();

            return match ( true ) {
                $role instanceof Role => Role::findById( $role->id, $area ),
                is_string( $role ) => Role::findByName( $role, $area ),
                is_int( $role ) => Role::findById( $role, $area )
            };
        }

        public function getRole(): Role|null {
            return $this->roles()->first();
        }
    }
