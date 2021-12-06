<?php

    namespace Tests\Unit\Payments;

    use Nila\Payments\Exceptions\PaymentsException;
    use Nila\Payments\Models\Wallet;
    use Tests\TestCase;

    class WalletModelTest extends TestCase {

        private Wallet $wallet;

        /**
         * Setup the test environment.
         *
         * @return void
         */
        protected function setUp(): void {
            parent::setUp();
            $this->wallet = Wallet::first();
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function walletVersioning() {
            $this->expectException( PaymentsException::class );
            $this->expectExceptionMessage( 'Instance version is not the latest!' );

            $wallet = Wallet::first(); // version 1
            $wallet->update( [ 'balance' => 100 ] ); // version 2
            $this->decrementWalletBalance(); // update to version 3 but doesn't affect the wallet instance in this scope
            $wallet->update( [ 'balance' => 100 ] ); // try to update out-of-dated instance
        }

        private function decrementWalletBalance(): void {
            Wallet::first()->update( [ 'balance' => 0 ] );
        }

    }
