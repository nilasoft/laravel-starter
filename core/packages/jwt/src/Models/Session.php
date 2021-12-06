<?php

    namespace Nila\Jwt\Models;

    use App\Models\User;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Support\Facades\Cache;
    use JwtCacheEnum;

    class Session extends Model {
        use HasFactory;

        protected $fillable = [ 'ip', 'device', 'platform', 'secret' ];

        protected static function booted() {
            self::created( function( self $model ) {
                Cache::forever( JwtCacheEnum::SESSION . $model->id, $model->getForCache() );
            } );
            self::updated( function( self $model ) {
                Cache::forget( JwtCacheEnum::SESSION . $model->id );
                Cache::forever( JwtCacheEnum::SESSION . $model->id, $model->getForCache() );
            } );
            self::deleted( function( self $model ) {
                Cache::forget( JwtCacheEnum::SESSION . $model->id );
            } );
        }

        public function getForCache() {
            return array_merge( $this->only( 'id', 'ip', 'device', 'platform', 'secret' ),
                [ 'userVersion' => $this->user->getVersion() ] );
        }

        public function user(): BelongsTo {
            return $this->belongsTo( User::class );
        }
    }
