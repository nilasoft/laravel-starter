<?php

    namespace Nila\Payments\Exceptions;

    class PaymentsErrorCode {
        public const BALANCE_NOT_ENOUGH = 3001;
        public const DUPLICATE_REQUEST = 3002;
        public const TOKEN_IS_INVALID = 3003;
        public const URL_IS_INVALID = 3004;
        public const TRANSACTION_IS_LOCKED = 3005;
        public const STATUS_UPDATE_FAILED = 3006;
        public const WALLET_IS_INACTIVE = 3007;
        public const CREATING_DEPOSIT_FAILED = 3008;
        public const CREATING_MANUAL_DEPOSIT_FAILED = 3009;
        public const CREATING_PURCHASE_FAILED = 3010;
        public const CREATING_WITHDRAW_FAILED = 3011;
        public const CREATING_MANUAL_WITHDRAW_FAILED = 3012;
        public const GATEWAY_NOT_EXISTS = 3013;
        public const ANOTHER_WITHDRAW_REQUEST_EXISTS = 3014;
        public const VERIFICATION_FAILED = 3015;
        public const GATEWAY_ERROR = 3016;
        public const INSTANCE_VERSION_IS_OUT_OF_DATE = 3017;
    }
