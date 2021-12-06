<?php

    namespace Nila\Payments;

    use Carbon\Carbon;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\DB;
    use Nila\Payments\Contracts\PaymentsContract;
    use Nila\Payments\Exceptions\PaymentsGatewayException;
    use Nila\Payments\Exceptions\PaymentsErrorCode;
    use Nila\Payments\Exceptions\PaymentsException;
    use Nila\Payments\Models\Dtos\DepositDto;
    use Nila\Payments\Models\Dtos\PurchaseDto;
    use Nila\Payments\Models\Dtos\WithdrawDto;
    use Nila\Payments\Models\Requests\DepositManualRequest;
    use Nila\Payments\Models\Requests\DepositRequest;
    use Nila\Payments\Models\Requests\PurchaseRequest;
    use Nila\Payments\Models\Requests\TransactionRequest;
    use Nila\Payments\Models\Requests\WithdrawManualRequest;
    use Nila\Payments\Models\Transactions\Deposit;
    use Nila\Payments\Models\Transactions\Purchase;
    use Nila\Payments\Models\Transactions\Withdraw;
    use Nila\Payments\Models\Wallet;
    use Nila\Resources\Contracts\ResourcesContract;
    use PaymentsStatusEnum;

    class PaymentsService implements PaymentsContract {
        private array $configuration;

        public function __construct() {
            $this->configuration = config( 'payments' );
        }

        // TODO: redirect strategy
        function deposit( string $gateway, int $amount, Wallet $wallet ): RedirectResponse {
            try {
                $this->gatewayExists( $gateway );
                $this->checkWallet( $wallet );
            } catch ( PaymentsException $e ) {
                throw new PaymentsGatewayException( $e->getMessage(), $e->getErrorCode() );
            }

            try {
                DB::beginTransaction();
                $depositRequest = DepositRequest::create( [
                    'wallet_id'      => $wallet->id,
                    'amount_request' => $amount,
                    'gateway'        => $gateway,
                    'status'         => PaymentsStatusEnum::PENDING
                ] );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsGatewayException( 'Creating a deposit request failed!',
                    PaymentsErrorCode::CREATING_DEPOSIT_FAILED );
            }

            return get_gateway_class( $gateway )->redirect( $depositRequest );
        }

        private function gatewayExists( string $gateway ): void {
            if ( ! gateway_class_exists( $gateway ) ) {
                throw new PaymentsException( 'Gateway not exists!', PaymentsErrorCode::GATEWAY_NOT_EXISTS );
            }
        }

        private function checkWallet( Wallet $wallet ): void {
            if ( ! $wallet->active ) {
                throw new PaymentsException( 'Wallet is not active!', PaymentsErrorCode::WALLET_IS_INACTIVE );
            }
        }

        private function getConfig( string $key ): array|string {
            return Arr::get( $this->configuration, $key );
        }

        // TODO: redirect strategy
        function depositAndPurchase( string $gateway, int $amount, Wallet $wallet, Model $model = null ): RedirectResponse {
            try {
                $this->gatewayExists( $gateway );
                $this->checkWallet( $wallet );
            } catch ( PaymentsException $e ) {
                throw new PaymentsGatewayException( $e->getMessage(), $e->getErrorCode() );
            }

            try {
                DB::beginTransaction();
                $purchase = PurchaseRequest::create( [
                    'wallet_id'      => $wallet->id,
                    'amount_request' => $amount,
                    'gateway'        => $gateway,
                    'status'         => PaymentsStatusEnum::PENDING,
                ] );
                if ( $model ) {
                    $purchase->purchasable()->associate( $model );
                }
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsGatewayException( 'Purchase failed to create!',
                    PaymentsErrorCode::CREATING_PURCHASE_FAILED );
            }

            return get_gateway_class( $gateway )->redirect( $purchase );
        }

        function verify( string $model, int $id ): Deposit|Purchase {
            $model = $this->resolveModel( $model, $id );
            $this->expirationCheck( $model );
            try {
                $this->gatewayExists( $model->gateway );
                $this->checkStatus( $model );
                $this->checkWallet( $model->wallet );
            } catch ( PaymentsException $e ) {
                throw new PaymentsGatewayException( $e->getMessage(), $e->getErrorCode() );
            }

            if ( get_gateway_class( $model->gateway )->verify( $model ) ) {
                return match ( true ) {
                    $model instanceof DepositRequest => $this->createDeposit( $model, $model->amount_request ),
                    $model instanceof PurchaseRequest => $this->createPurchase( $model, $model->amount_request ),
                };
            } else {
                $model->updateStatus( PaymentsStatusEnum::REJECTED );
                throw new PaymentsGatewayException( 'Verification failed!', PaymentsErrorCode::VERIFICATION_FAILED );
            }
        }

        private function resolveModel( string $model, int $id ): PurchaseRequest|DepositRequest {
            return call_user_func( "Nila\\Payments\\Models\\Requests\\$model::findOrFail", $id );
        }

        private function checkStatus( Model $model ): void {
            if ( in_array( $model?->status, [
                PaymentsStatusEnum::APPROVED,
                PaymentsStatusEnum::FAILED,
                PaymentsStatusEnum::REJECTED,
            ] ) ) {
                throw new PaymentsException( 'Request impossible!', PaymentsErrorCode::TRANSACTION_IS_LOCKED );
            }
        }

        private function createDeposit( DepositRequest|DepositManualRequest $depositRequest, int $amount ): Deposit {
            try {
                DB::beginTransaction();
                $depositRequest->update( [
                    'status' => PaymentsStatusEnum::APPROVED,
                    'amount' => $amount
                ] );
                $resultModel = $depositRequest->transactions()->create( [
                    'wallet_id' => $depositRequest->wallet_id,
                    'amount'    => $amount,
                ] );
                $depositRequest->wallet()->increment( 'balance', $amount );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsException( 'Failed to create the deposit!',
                    PaymentsErrorCode::CREATING_DEPOSIT_FAILED );
            }

            return $resultModel;
        }

        private function createPurchase( PurchaseRequest $purchase_request, int $amount ): Purchase {
            try {
                DB::beginTransaction();
                $purchase_request->update( [
                    'status' => PaymentsStatusEnum::APPROVED,
                    'amount' => $amount
                ] );
                $purchase_request->depositTransaction()->create( [
                    'wallet_id' => $purchase_request->wallet_id,
                    'amount'    => $amount,
                ] );
                $resultModel = $purchase_request->purchaseTransaction()->create( [
                    'wallet_id' => $purchase_request->wallet_id,
                    'amount'    => $amount,
                ] );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsException( 'Failed to create the purchase!',
                    PaymentsErrorCode::CREATING_PURCHASE_FAILED );
            }

            return $resultModel;
        }

        function depositFailed( DepositRequest|PurchaseRequest $model ): DepositRequest|PurchaseRequest {
            $this->checkStatus( $model );

            try {
                DB::beginTransaction();
                $model->update( [ 'status' => PaymentsStatusEnum::FAILED ] );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsException( 'an Error occur due to changing the deposit status to failed!',
                    PaymentsErrorCode::STATUS_UPDATE_FAILED );
            }

            return $model;
        }

        private function expirationCheck( TransactionRequest $request ): void {
            if ( Carbon::now() > Carbon::instance( $request->created_at )
                                       ->modify( $this->getConfig( 'expiration' ) ) ) {
                $request->updateStatus( PaymentsStatusEnum::REJECTED );
                throw new PaymentsException( 'Request expired!', PaymentsErrorCode::STATUS_UPDATE_FAILED );
            }
        }

        function depositManual( DepositDto $depositDto, string $field ): DepositManualRequest {
            $this->checkWallet( $wallet = $depositDto->getWallet() );

            $receiptId = app( ResourcesContract::class )->upload( $field )->getId();
            try {
                DB::beginTransaction();
                $deposit = DepositManualRequest::create( [
                    'wallet_id'      => $wallet->id,
                    'amount_request' => $depositDto->getAmount(),
                    'gateway'        => $depositDto->getGateway(),
                    'status'         => PaymentsStatusEnum::PENDING,
                ] );
                $deposit->uploads()->sync( [ $receiptId ] );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                app( ResourcesContract::class )->delete( $receiptId );
                throw new PaymentsException( 'Failed to create a manual deposit!',
                    PaymentsErrorCode::CREATING_MANUAL_DEPOSIT_FAILED );
            }

            return $deposit;
        }

        function depositManualApprove( DepositManualRequest $depositManualRequest, int $amount ): Deposit {
            $this->checkStatus( $depositManualRequest );

            return $this->createDeposit( $depositManualRequest, $amount );
        }

        function depositManualReject( DepositManualRequest $depositManualRequest ): DepositManualRequest {
            $this->checkStatus( $depositManualRequest );

            try {
                DB::beginTransaction();
                $depositManualRequest->update( [ 'status' => PaymentsStatusEnum::REJECTED ] );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsException( 'Failed to reject the deposit!', PaymentsErrorCode::STATUS_UPDATE_FAILED );
            }

            return $depositManualRequest;
        }

        function withdrawManual( WithdrawDto $withdrawDto ): WithdrawManualRequest {
            $this->pendingWithdrawExists( $wallet = $withdrawDto->getWallet() );
            $this->checkWallet( $wallet );
            $this->checkFinalBalance( $wallet, $withdrawDto->getAmount() );

            try {
                DB::beginTransaction();
                $withdraw = WithdrawManualRequest::create( [
                    'wallet_id'      => $wallet->id,
                    'amount_request' => $withdrawDto->getAmount(),
                    'gateway'        => $withdrawDto->getGateway(),
                    'status'         => PaymentsStatusEnum::PENDING,
                ] );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsException( 'Failed to create a manual withdraw!',
                    PaymentsErrorCode::CREATING_MANUAL_WITHDRAW_FAILED );
            }

            return $withdraw;
        }

        private function pendingWithdrawExists( Wallet $wallet ): void {
            if ( $wallet->hasPendingWithdrawRequest() ) {
                throw new PaymentsException( 'You have another pending withdraw request!',
                    PaymentsErrorCode::ANOTHER_WITHDRAW_REQUEST_EXISTS );
            }
        }

        // approveWithdraw
        private function checkBalance( Wallet $wallet, int $amount ): void {
            if ( $wallet->balance < $amount ) {
                throw new PaymentsException( 'Balance not enough!', PaymentsErrorCode::BALANCE_NOT_ENOUGH );
            }
        }

        private function checkFinalBalance( Wallet $wallet, int $amount ): void {
            if ( $wallet->getBalance() < $amount ) {
                throw new PaymentsException( 'Balance not enough!', PaymentsErrorCode::BALANCE_NOT_ENOUGH );
            }
        }

        function withdrawManualApprove( WithdrawManualRequest $withdrawManualRequest, int $amount ): Withdraw {
            $this->checkStatus( $withdrawManualRequest );
            $this->checkWallet( $wallet = $withdrawManualRequest->wallet );
            $this->checkBalance( $wallet, $amount );

            return $this->createWithdraw( $withdrawManualRequest, $amount );
        }

        private function createWithdraw( WithdrawManualRequest $withdrawManualRequest, int $amount ): Withdraw {
            try {
                DB::beginTransaction();
                $withdrawManualRequest->update( [
                    'status' => PaymentsStatusEnum::APPROVED,
                    'amount' => $amount
                ] );
                $withdraw = $withdrawManualRequest->transactions()->create( [
                    'wallet_id' => $withdrawManualRequest->wallet_id,
                    'amount'    => $amount
                ] );
                $withdrawManualRequest->wallet()->decrement( 'balance', $amount );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsException( 'Failed to create a withdraw!',
                    PaymentsErrorCode::CREATING_WITHDRAW_FAILED );
            }

            return $withdraw;
        }

        function withdrawManualReject( WithdrawManualRequest $withdrawManualRequest ): WithdrawManualRequest {
            $this->checkStatus( $withdrawManualRequest );

            try {
                DB::beginTransaction();
                $withdrawManualRequest->update( [ 'status' => PaymentsStatusEnum::REJECTED ] );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsException( 'Failed to reject the withdraw!',
                    PaymentsErrorCode::STATUS_UPDATE_FAILED );
            }

            return $withdrawManualRequest;
        }

        function purchase( PurchaseDto $purchaseDto, Model $model = null ): Purchase {
            $this->checkWallet( $wallet = $purchaseDto->getWallet() );
            $this->checkFinalBalance( $wallet, $purchaseDto->getAmount() );
            try {
                DB::beginTransaction();
                $purchaseRequest = PurchaseRequest::create( [
                    'wallet_id'      => $purchaseDto->wallet->id,
                    'amount_request' => $purchaseDto->getAmount(),
                    'amount'         => $purchaseDto->getAmount(),
                    'gateway'        => $purchaseDto->getGateway(),
                    'status'         => PaymentsStatusEnum::APPROVED,
                ] );
                if ( $model ) {
                    $purchaseRequest->purchasable()->associate( $model )->save();
                }
                $purchase = $purchaseRequest->purchaseTransaction()->create( [
                    'wallet_id' => $purchaseRequest->wallet_id,
                    'amount'    => $purchaseRequest->amount,
                ] );
                $purchase->wallet()->decrement( 'balance', $purchase->amount );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsException( 'Failed to create the Purchase!',
                    PaymentsErrorCode::STATUS_UPDATE_FAILED );
            }

            return $purchase;
        }
    }
