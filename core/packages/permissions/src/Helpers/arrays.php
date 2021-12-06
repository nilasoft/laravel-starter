<?php

    if ( ! function_exists( 'roles' ) ) {
        function roles():array {
            return RolesEnum::toArray();
        }
    }

    if ( ! function_exists( 'areas' ) ) {
        function areas():array {
            return AreasEnum::toArray();
        }
    }
