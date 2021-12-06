<?php

    namespace Tests\Feature\Payments;

    use App\Models\Preference;
    use Illuminate\Http\UploadedFile;
    use Nila\Payments\Exceptions\PaymentsException;
    use Nila\Payments\Exceptions\PaymentsGatewayException;
    use Nila\Payments\Models\Dtos\DepositDto;
    use Nila\Payments\Models\Dtos\PurchaseDto;
    use Nila\Payments\Models\Dtos\WithdrawDto;
    use Nila\Payments\Models\Requests\DepositManualRequest;
    use Nila\Payments\Models\Requests\DepositRequest;
    use Nila\Payments\Models\Requests\PurchaseRequest;
    use Nila\Payments\Models\Requests\WithdrawManualRequest;
    use Nila\Payments\Models\Transactions\Deposit;
    use Nila\Payments\Models\Transactions\Purchase;
    use Nila\Payments\Models\Transactions\Withdraw;
    use Nila\Payments\Models\Wallet;
    use Nila\Resources\Models\Resource;
    use PaymentsStatusEnum;
    use Tests\Factories\PaymentsFactory;
    use Tests\TestCase;

    class PaymentsContractTest extends TestCase {
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
        public function deposit() {
            $gateway = $this->paymentsConfig[ 'apis' ][ 'default' ];
            $deposit = $this->payments->deposit( $gateway, 10000, $this->wallet );

            $mode = $this->paymentsConfig[ 'apis' ][ $gateway ][ 'mode' ];
            $url  = $this->paymentsConfig[ 'apis' ][ $gateway ][ $mode ][ 'url' ];

            $this->assertStringContainsString( $url, $deposit->getTargetUrl() );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function depositInvalidGateway() {
            $this->expectException( PaymentsGatewayException::class );
            $this->expectExceptionMessage( 'Gateway not exists!' );
            $this->payments->deposit( 'CR7', 10000, $this->wallet );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function depositDisableWallet() {
            $gateway = $this->paymentsConfig[ 'apis' ][ 'default' ];
            $this->wallet->update( [ 'active' => false ] );

            $this->expectException( PaymentsGatewayException::class );
            $this->expectExceptionMessage( 'Wallet is not active!' );
            $this->payments->deposit( $gateway, 10000, $this->wallet );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function depositAndPurchase() {
            $gateway = $this->paymentsConfig[ 'apis' ][ 'default' ];
            $deposit = $this->payments->depositAndPurchase( $gateway, 10000, $this->wallet );

            $mode = $this->paymentsConfig[ 'apis' ][ $gateway ][ 'mode' ];
            $url  = $this->paymentsConfig[ 'apis' ][ $gateway ][ $mode ][ 'url' ];

            $this->assertStringContainsString( $url, $deposit->getTargetUrl() );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function depositAndPurchaseInvalidGateway() {
            $this->expectException( PaymentsGatewayException::class );
            $this->expectExceptionMessage( 'Gateway not exists!' );
            $this->payments->depositAndPurchase( 'CR7', 10000, $this->wallet );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function depositAndPurchaseDisableWallet() {
            $gateway = $this->paymentsConfig[ 'apis' ][ 'default' ];
            $this->wallet->update( [ 'active' => false ] );

            $this->expectException( PaymentsGatewayException::class );
            $this->expectExceptionMessage( 'Wallet is not active!' );
            $this->payments->depositAndPurchase( $gateway, 10000, $this->wallet );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function purchase() {
            $this->wallet->update( [ 'balance' => 1000 ] );
            $model    = Preference::first();
            $purchase = $this->payments->purchase( PurchaseDto::make( $this->wallet, 1000 ), $model );

            $this->assertInstanceOf( Preference::class, $purchase->request->associated() );
            $this->assertEquals( $model, $purchase->request->associated() );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function purchaseWalletCheckBalanceNotEnough() {
            $this->expectException( PaymentsException::class );
            $this->expectExceptionMessage( 'Balance not enough!' );
            $this->payments->purchase( PurchaseDto::make( $this->wallet, 1000 ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function purchaseWalletCheckWalletIsInactive() {
            $this->wallet->update( [ 'active' => false ] );

            $this->expectException( PaymentsException::class );
            $this->expectExceptionMessage( 'Wallet is not active!' );
            $this->payments->purchase( PurchaseDto::make( $this->wallet, 1000 ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function verifyingDepositRequest() {
            $depositRequest = PaymentsFactory::depositRequest();

            $deposit = $this->payments->verify( class_basename( $depositRequest ), $depositRequest->id );
            $this->assertInstanceOf( Deposit::class, $deposit );

            $this->assertEquals( $depositRequest->id, $deposit->request()->id );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function verifyingDepositRequestInvalidGateway() {
            $depositRequest = PaymentsFactory::depositRequest( DepositDto::make( $this->wallet, 10000, 'CR7' ) );

            $this->expectException( PaymentsGatewayException::class );
            $this->expectExceptionMessage( 'Gateway not exists!' );

            $this->payments->verify( class_basename( $depositRequest ), $depositRequest->id );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function verifyingDepositRequestInvalidStatus() {
            $depositRequest = PaymentsFactory::depositRequest( DepositDto::make( $this->wallet, 10000,
                status: PaymentsStatusEnum::REJECTED ) );

            $this->expectException( PaymentsGatewayException::class );
            $this->expectExceptionMessage( 'Request impossible!' );

            $this->payments->verify( class_basename( $depositRequest ), $depositRequest->id );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function verifyingDepositRequestInactiveWallet() {
            $this->wallet->update( [ 'active' => false ] );
            $depositRequest = PaymentsFactory::depositRequest( DepositDto::make( $this->wallet, 10000 ) );

            $this->expectException( PaymentsGatewayException::class );
            $this->expectExceptionMessage( 'Wallet is not active!' );

            $this->payments->verify( class_basename( $depositRequest ), $depositRequest->id );
        }


        /**
         * @test
         *
         *
         * @return void
         */
        public function verifyingPurchaseRequest() {
            $purchaseRequest = PaymentsFactory::purchaseRequest();

            $purchase = $this->payments->verify( class_basename( $purchaseRequest ), $purchaseRequest->id );
            $this->assertInstanceOf( Purchase::class, $purchase );

            $this->assertEquals( $purchaseRequest->id, $purchase->request->id );
        }


        /**
         * @test
         *
         *
         * @return void
         */
        public function depositFailed() {
            $depositRequest = PaymentsFactory::depositRequest();

            $this->assertInstanceOf( DepositRequest::class,
                $failed = $this->payments->depositFailed( $depositRequest ) );
            $this->assertEquals( PaymentsStatusEnum::FAILED, $failed->status );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function purchaseFailed() {
            $purchaseRequest = PaymentsFactory::purchaseRequest();

            $this->assertInstanceOf( PurchaseRequest::class,
                $failed = $this->payments->depositFailed( $purchaseRequest ) );
            $this->assertEquals( PaymentsStatusEnum::FAILED, $failed->status );

        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function purchaseFailedInvalidStatus() {
            $purchaseRequest = PaymentsFactory::purchaseRequest( new PurchaseDto( $this->wallet, 1000,
                status: PaymentsStatusEnum::REJECTED ) );

            $this->expectException( PaymentsException::class );
            $this->expectExceptionMessage( 'Request impossible!' );

            $this->payments->depositFailed( $purchaseRequest );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function depositManual() {
            request()->files->set( $field = 'receipt',
                UploadedFile::fake()->image( 'receipt-' . rand( 10, 99 ) . '.jpg', 500, 500 ) );

            $depositManual = $this->payments->depositManual( new DepositDto( $this->wallet, 10000 ), $field );

            $this->assertInstanceOf( DepositManualRequest::class, $depositManual );
            $this->assertInstanceOf( Resource::class, $depositManual->uploads->first() );

            $this->resources->delete( $depositManual->uploads->first()->id );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function depositManualApprove() {
            $depositRequest = PaymentsFactory::manualDepositRequest( new DepositDto( $this->wallet, 1500 ) );

            $deposit = $this->payments->depositManualApprove( $depositRequest, 1000 );

            $this->assertInstanceOf( Deposit::class, $deposit );
            $this->assertEquals( 1000, $this->wallet->fresh()->getBalance() );
        }


        /**
         * @test
         *
         *
         * @return void
         */
        public function depositManualApproveInvalidStatus() {
            $depositRequest = PaymentsFactory::manualDepositRequest( new DepositDto( $this->wallet, 1500,
                status: PaymentsStatusEnum::REJECTED ) );

            $this->expectException( PaymentsException::class );
            $this->expectExceptionMessage( 'Request impossible!' );

            $this->payments->depositManualApprove( $depositRequest, 1000 );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function depositManualReject() {
            $depositRequest = PaymentsFactory::manualDepositRequest( new DepositDto( $this->wallet, 1500 ) );

            $DepositManualRequest = $this->payments->depositManualReject( $depositRequest );

            $this->assertInstanceOf( DepositManualRequest::class, $DepositManualRequest );
            $this->assertEquals( PaymentsStatusEnum::REJECTED, $DepositManualRequest->status );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function depositManualRejectInvalidStatus() {
            $depositRequest = PaymentsFactory::manualDepositRequest( new DepositDto( $this->wallet, 1500,
                status: PaymentsStatusEnum::FAILED ) );

            $this->expectException( PaymentsException::class );
            $this->expectExceptionMessage( 'Request impossible!' );

            $this->payments->depositManualReject( $depositRequest );
        }


        /**
         * @test
         *
         *
         * @return void
         */
        public function withdrawManual() {
            $this->wallet->update( [ 'balance' => 10000 ] );
            $withdrawManualRequest = $this->payments->withdrawManual( new WithdrawDto( $this->wallet, 10000 ) );

            $this->assertInstanceOf( WithdrawManualRequest::class, $withdrawManualRequest );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function withdrawManualAnotherPendingRequestExists() {
            $this->wallet->update( [ 'balance' => 10000 ] );
            $this->payments->withdrawManual( new WithdrawDto( $this->wallet, 10000 ) );


            $this->expectException( PaymentsException::class );
            $this->expectExceptionMessage( 'You have another pending withdraw request!' );

            $this->payments->withdrawManual( new WithdrawDto( $this->wallet, 10000 ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function withdrawManualWalletFinalBalanceValidation() {
            $this->wallet->update( [ 'balance' => 10000 ] );

            $this->expectException( PaymentsException::class );
            $this->expectExceptionMessage( 'Balance not enough!' );

            $this->payments->withdrawManual( new WithdrawDto( $this->wallet, 10001 ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function withdrawManualApprove() {
            $withdrawRequest = PaymentsFactory::withdrawRequest();
            $this->wallet->update( [ 'balance' => 1000 ] );

            $withdraw = $this->payments->withdrawManualApprove( $withdrawRequest, 1000 );
            $this->assertInstanceOf( Withdraw::class, $withdraw );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function withdrawManualApproveBalanceNotEnough() {
            $this->expectException( PaymentsException::class );
            $this->expectExceptionMessage( 'Balance not enough!' );

            $withdrawRequest = PaymentsFactory::withdrawRequest();
            $this->payments->withdrawManualApprove( $withdrawRequest, 1000 );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function withdrawManualReject() {
            $withdrawRequest = PaymentsFactory::withdrawRequest();
            $failed          = $this->payments->withdrawManualReject( $withdrawRequest );

            $this->assertEquals( PaymentsStatusEnum::REJECTED, $failed->status );
        }
    }
