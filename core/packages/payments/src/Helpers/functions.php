<?php

    if ( ! function_exists( 'get_gateway_class' ) ) {
        function get_gateway_class( string $gateway ): \Nila\Payments\Gateways\Contracts\PaymentsGateway|bool {
            $gateways = get_gateway_list();
            if ( in_array( $gateway, array_keys( $gateways ) ) ) {
                return new ( $gateways[ $gateway ] )();
            }

            return false;
        }
    }

    if ( ! function_exists( 'gateway_class_exists' ) ) {
        function gateway_class_exists( string $gateway ): bool {
            if ( in_array( $gateway, array_keys( get_gateway_list() ) ) ) {
                return true;
            }

            return false;
        }
    }
