<?php

    namespace Nila\Menus\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Cache;
    use MenusCacheEnum;
    use Nila\Permissions\HasPermissions;
    use Nila\Permissions\Models\Traits\HasRelations;

    class Menu extends Model {
        use HasFactory;
        use HasPermissions, HasRelations;

        protected $fillable = [
            'order',
            'parent_id',

            'title',
            'icon',
            'class',
            'link',

            'key'
        ];

        protected $casts = [
            'order' => 'int'
        ];

        /**
         * Perform any actions required after the model boots.
         *
         * @return void
         */
        protected static function booted() {
            self::saved( function( self $model ) {
                Cache::forever( MenusCacheEnum::PERMISSIONS . $model->id, $model->getPermissionNames()->toArray() );
            } );
            self::deleted( function( self $model ) {
                Cache::forget( MenusCacheEnum::PERMISSIONS . $model->id );
            } );
        }

        public static function keyExists( string $key ): bool {
            return self::query()->select( 'id' )->where( 'key', $key )->exists();
        }

        public function children() {
            return $this->hasMany( self::class, 'parent_id' );
        }

        public function parent() {
            return $this->belongsTo( self::class, 'parent_id' );
        }

        // recursive, loads all descendants
        public function descendants() {
            return $this->children()->with( [ 'descendants', 'permissions' ] )->orderBy( 'order' );
        }

        public static function generate( string $key = null ) {
            $query = self::with( [ 'descendants', 'permissions' ] );
            if ( $key ) {
                $query->where( 'key', $key );
            }else{
                $query->whereNull( 'parent_id' );
            }

            return $query->orderBy( 'order' );
        }
    }
