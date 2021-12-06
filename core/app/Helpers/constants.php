<?php

    if ( ! class_exists( Enum::class ) ) {
        abstract class Enum {
            public static function toArray() {
                $vars = collect();
                foreach ( ( new ReflectionClass( static::class ) )->getConstants() as $name => $value ):
                    $vars->put( $name, $value );
                endforeach;

                return $vars->toArray();
            }
        }
    }

    class CacheEnum extends Enum {
        const PREFIX = 'STARTER_PREFIX_';
    }
