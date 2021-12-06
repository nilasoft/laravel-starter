<?php

use Nila\Payments\Gateways\PayirApi;

if (!function_exists('get_gateway_list')) {
    function get_gateway_list(): array
    {
        return [
            PaymentsEnum::PAYIR => PayirApi::class
        ];
    }
}
