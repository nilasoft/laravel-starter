<?php


    namespace Nila\Resources;

    use Nila\Resources\Contracts\SignatureContract;

    class SignatureService implements SignatureContract {
        private string $secret;

        public function __construct( string $secret ) {
            $this->secret = $secret;
        }

        public function create(): string {
            return hash_hmac( 'ripemd160', $this->key(), $this->secret );
        }

        public function isValid( string $signature ): bool {
            return $this->create() == $signature;
        }

        public function isNotValid( string $signature ): bool {
            return ! $this->isValid( $signature );
        }

        public function key(): string {
            return request()->ip() . request()->userAgent();
        }
    }
