<?php

    namespace Nila\Permissions\Models;

    use Nila\Permissions\Exceptions\PermissionsErrorCode;
    use Nila\Permissions\Exceptions\PermissionsException;
    use Nila\Permissions\Models\Contracts\Role as RoleContract;
    use Nila\Permissions\HasPermissions;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Support\Facades\Cache;
    use PermissionsCacheEnum;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    class Role extends Model implements RoleContract {
        use HasPermissions;

        protected $fillable = [ 'name', 'area', 'version' ];
        protected $casts = [ 'version' => 'integer' ];

        protected static function booted() {
            static::created( function( self $model ) {
                Cache::forever( PermissionsCacheEnum::ROLE . $model->id, $model );
            } );
            static::updated( function( self $model ) {
                $model->increaseVersion();
            } );
            static::deleted( function( self $model ) {
                Cache::forget( PermissionsCacheEnum::ROLE . $model->id );
            } );
        }


        public function permissions(): BelongsToMany {
            return $this->belongsToMany( Permission::class );
        }

        /**
         * @param string      $name
         * @param string|null $area
         *
         * @return static
         * @throws PermissionsException
         */
        public static function findByName( string $name, string $area = null ): self {
            $role = self::query();
            if ( $area ) {
                $role = $role->whereArea( $area );
            }
            $role = $role->whereName( $name )->first();
            if ( ! $role ) {
                $area = ! $area ? 'all areas' : $area . ' area';
                throw new PermissionsException( "The $name role not found in the $area!",
                    PermissionsErrorCode::ROLE_NOT_FOUND, ResponseAlias::HTTP_NOT_FOUND );
            }

            return $role;
        }

        /**
         * @param int         $id
         * @param string|null $area
         *
         * @return static
         * @throws PermissionsException
         */
        public static function findById( int $id, string $area = null ): self {
            $role = self::query();
            if ( $area ) {
                $role = $role->whereArea( $area );
            }

            $role = $role->find( $id );

            if ( ! $role ) {
                $area = ! $area ? 'all areas' : $area . ' area';
                throw new PermissionsException( "The $id role not found in the $area!",
                    PermissionsErrorCode::ROLE_NOT_FOUND, ResponseAlias::HTTP_NOT_FOUND );
            }

            return $role;
        }

        /**
         * @param string $name
         * @param string $area
         *
         * @return self
         */
        public static function findOrCreate( string $name, string $area ): self {
            $role = self::whereName( $name )->whereArea( $area )->first();

            if ( $role ) {
                return $role;
            }

            return self::create( compact( 'name', 'area' ) );
        }

        public function getVersion(): int {
            return $this->version;
        }

        public function increaseVersion(): bool {
            try {
                $this->forceFill( [ 'version' => $this->getVersion() + 1 ] )->saveQuietly();
                Cache::forget( PermissionsCacheEnum::ROLE . $this->id );
                Cache::forever( PermissionsCacheEnum::ROLE . $this->id, $this );
            } catch ( \Throwable $e ) {
                return false;
            }

            return true;
        }
    }
