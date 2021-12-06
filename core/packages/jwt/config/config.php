<?php
    return [
        'private_key'        => env( 'JWT_PRIVATE_KEY', 'mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw=' ),
        'expired_at'        => '+1 hour',
        'refreshExpired_at' => '+2 hour'
    ];
