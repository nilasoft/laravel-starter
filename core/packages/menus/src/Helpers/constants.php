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

    class MenusCacheEnum extends Enum {
        public const PERMISSIONS = 'cache_menus_permissions_';
        public const MENU = 'menu_cache_';
        public const MENU_ALL = 'menu_cache_*';
    }
