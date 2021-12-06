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

    class RolesEnum extends Enum {
        const DEFAULT_USERS = 'user';
        const DEFAULT_ADMINS = 'admin';
    }

    class AreasEnum extends Enum {
        const ADMIN = 'employee';
        const USER = 'customer';
    }

    class PermissionsCacheEnum extends Enum {
        public const ROLE = 'role_cache_';
    }

