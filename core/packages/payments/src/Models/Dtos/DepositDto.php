<?php

    namespace Nila\Payments\Models\Dtos;

    use Illuminate\Support\Arr;
    use Nila\Payments\Models\Wallet;
    use PaymentsStatusEnum;

    class DepositDto {
        public Wallet $wallet;
        public int $amount;
        public string $gateway;
        public string $status;

        protected array $configuration;

        /**
         * @param Wallet      $wallet
         * @param int         $amount
         * @param string|null $gateway
         * @param string|null $status
         */
        public function __construct( Wallet $wallet, int $amount, string $gateway = null, string $status = null ) {
            $this->configuration = config( 'payments' );
            $this->wallet        = $wallet;
            $this->amount        = $amount;
            $this->gateway       = $gateway ? : $this->getConfig( 'apis.default' );
            $this->status        = $status ? : PaymentsStatusEnum::PENDING;
        }

        private function getConfig( string $key ): array|string {
            return Arr::get( $this->configuration, $key );
        }

        public static function make( Wallet $wallet, int $amount, string $gateway = null, string $status = null ): self {
            return new self( $wallet, $amount, $gateway, $status );
        }

        /**
         * @return Wallet
         */
        public function getWallet(): Wallet {
            return $this->wallet;
        }

        /**
         * @param Wallet $wallet
         *
         * @return self
         */
        public function setWallet( Wallet $wallet ): self {
            $this->wallet = $wallet;

            return $this;
        }

        /**
         * @return int
         */
        public function getAmount(): int {
            return $this->amount;
        }

        /**
         * @param int $amount
         *
         * @return self
         */
        public function setAmount( int $amount ): self {
            $this->amount = $amount;

            return $this;
        }

        /**
         * @return string
         */
        public function getGateway(): string {
            return $this->gateway;
        }

        /**
         * @param array|string $gateway
         *
         * @return self
         */
        public function setGateway( array|string $gateway ): self {
            $this->gateway = $gateway;

            return $this;
        }

        /**
         * @return string
         */
        public function getStatus(): string {
            return $this->status;
        }

        /**
         * @param string $status
         *
         * @return self
         */
        public function setStatus( string $status ): self {
            $this->status = $status;

            return $this;
        }


    }
