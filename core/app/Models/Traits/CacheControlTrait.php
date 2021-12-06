<?php

    namespace App\Models\Traits;

    use Illuminate\Support\Facades\Cache;
    use JwtCacheEnum;

    trait CacheControlTrait {
        protected static function booted() {
            static::updated( function( self $model ) {
                if ( method_exists( $model, 'sessions' ) ) {
                    $model->increaseVersion();
                    $userVersion = $model->getVersion();
                    collect( $model->sessions )->each( function( $session ) use ( $userVersion ) {
                        Cache::forget( $key = JwtCacheEnum::SESSION . $session->id );
                        Cache::forever( $key, array_merge( $session->toArray(), compact( 'userVersion' ) ) );
                    } );
                }
            } );
        }
    }
