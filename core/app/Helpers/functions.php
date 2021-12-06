<?php

    use App\Models\Preference;

    if ( ! function_exists( 'user' ) ) {
        function user(): \App\Models\User|bool {
            return Auth::check() ? Auth::user() : false;
        }
    }

    if ( ! function_exists( 'get_preference' ) ) {
        function get_preference( string $key, bool $fresh = false ) {
            if ( $fresh ) {
                try {
                    Cache::forever( CacheEnum::PREFIX . $key, $value = Preference::firstWhere( 'key', $key )->value );

                    return $value;
                } catch ( \Throwable $e ) {

                    return null;
                }
            }

            if ( $value = Cache::get( CacheEnum::PREFIX . $key ) ) {
                return $value;
            } else {
                try {
                    Cache::forever( CacheEnum::PREFIX . $key, $value = Preference::firstWhere( 'key', $key )->value );

                    return $value;
                } catch ( \Throwable $e ) {
                    return null;
                }
            }
        }
    }

    if ( ! function_exists( 'set_preference' ) ) {
        function set_preference( string $key, $value ): bool {
            try {
                $value = Preference::updateOrCreate( [ 'key' => $key ], [ 'value' => $value ] )->value;
                Cache::forever( CacheEnum::PREFIX . $key, $value );
            } catch ( \Throwable $e ) {
                return false;
            }

            return true;
        }
    }

    if ( ! function_exists( 'set_batch_preferences' ) ) {
        function set_batch_preferences( array $preferences ): bool {
            try {
                foreach ( $preferences as $key => $value ) {
                    set_preference( $key, $value );
                }
            } catch ( \Throwable $e ) {
                return false;
            }

            return true;
        }
    }


