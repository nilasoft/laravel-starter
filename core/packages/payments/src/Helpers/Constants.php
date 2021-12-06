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

    class PaymentsStatusEnum extends Enum {
        const PENDING = 'pending';
        const APPROVED = 'approved';
        const REJECTED = 'rejected';
        const FAILED = 'failed';
    }


    class PaymentsEnum extends Enum {
        const PAYIR = 'payir';
        const PAYPAL = 'paypal';
    }

    class TransactionsEnum extends Enum {
        const DEPOSIT = 'deposit';
        const WITHDRAW = 'withdraw';
        const PURCHASE = 'purchase';
    }

    class TransactionRequestsEnum extends Enum {
        const DEPOSIT = 'deposit';
        const WITHDRAW = 'withdraw';
        const PURCHASE = 'purchase';
        const WITHDRAW_MANUAL = 'withdraw_manual';
        const DEPOSIT_MANUAL = 'deposit_manual';
    }
