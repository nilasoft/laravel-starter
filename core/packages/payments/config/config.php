<?php


    return [
        /*
        |--------------------------------------------------------------------------
        | Apis defines here
        |--------------------------------------------------------------------------
        |
        | you can register and set your needed keys for each gateway
        |
        */
        'apis'       => [
            'default'           => PaymentsEnum::PAYIR,
            PaymentsEnum::PAYIR => [
                'mode'       => 'sandbox',
                'sandbox'    => [
                    'key'    => 'test',
                    'url'    => 'https://pay.ir/pg/',
                    'send'   => 'https://pay.ir/pg/send',
                    'verify' => 'https://pay.ir/pg/verify'
                ],
                'production' => [
                    'key'    => '',
                    'url'    => 'https://pay.ir/pg/',
                    'send'   => 'https://pay.ir/pg/send',
                    'verify' => 'https://pay.ir/pg/verify'
                ]
            ]
        ],
        /*
        |--------------------------------------------------------------------------
        | Callback route
        |--------------------------------------------------------------------------
        |
        | this callback route used for passing to the gateway
        |
        */
        'callback'   => '/{model}/{id}',
        /*
        |--------------------------------------------------------------------------
        | Default currency
        |--------------------------------------------------------------------------
        |
        | the default currency for user's wallet.
        |
        */
        'currency'   => 'IRR',
        /*
        |--------------------------------------------------------------------------
        | Expiration
        |--------------------------------------------------------------------------
        |
        | payment requests expired after this time period,
        | if their status still be pending.
        |
        */
        'expiration' => env('PAYMENTS_EXPIRATION','+40 minute'),
        /*
        |--------------------------------------------------------------------------
        | Pending Payments Job
        |--------------------------------------------------------------------------
        |
        | this job find payment requests that their status didn't change and
        | created time is more than expiration time period.
        | you can switch on/off this job.
        |
        */
        'scheduler'        => env( 'PAYMENTS_SCHEDULER', true )
    ];
