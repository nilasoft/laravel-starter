<?php

    namespace Nila\Permissions\Models;

    use Nila\Permissions\Exceptions\PermissionsErrorCode;
    use Nila\Permissions\Exceptions\PermissionsException;
    use Nila\Permissions\Models\Contracts\Permission as PermissionContract;
    use Nila\Permissions\HasRoles;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    class Permission extends Model implements PermissionContract {
        use HasRoles;

        protected $fillable = [ 'name', 'area' ];

        public function roles(): BelongsToMany {
            return $this->belongsToMany( Role::class );
        }

        /**
         * @param string      $name
         * @param string|null $area
         *
         * @return Permission
         * @throws PermissionsException
         */
        public static function findByName( string $name, string $area = null ): self {
            $permission = self::query();
            if ( $area ) {
                $permission = $permission->whereArea( $area );
            }
            $permission = $permission->whereName( $name )->first();

            if ( ! $permission ) {
                $area = ! $area ? 'all areas' : $area . ' area';
                throw new PermissionsException( "The $name permission not found in the $area!",
                    PermissionsErrorCode::PERMISSION_NOT_FOUND, ResponseAlias::HTTP_NOT_FOUND );
            }

            return $permission;
        }

        /**
         * @param int         $id
         * @param string|null $area
         *
         * @return Permission
         * @throws PermissionsException
         */
        public static function findById( int $id, string $area = null ): self {
            $permission = self::query();
            if ( $area ) {
                $permission = $permission->whereArea( $area );
            }
            $permission = $permission->find( $id );

            if ( ! $permission ) {
                $area = ! $area ? 'all areas' : $area . ' area';
                throw new PermissionsException( "The $id permission not found in the $area!",
                    PermissionsErrorCode::PERMISSION_NOT_FOUND, ResponseAlias::HTTP_NOT_FOUND );
            }

            return $permission;
        }

        /**
         * @param string $name
         * @param string $area
         *
         * @return Permission
         */
        public static function findOrCreate( string $name, string $area ): self {
            $permission = self::whereName( $name )->whereArea( $area )->first();

            if ( $permission ) {
                return $permission;
            }

            return self::create( compact( 'name', 'area' ) );
        }
    }
