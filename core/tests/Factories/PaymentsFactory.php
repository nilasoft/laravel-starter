<?php

    namespace Tests\Factories;

    use Nila\Payments\Models\Dtos\DepositDto;
    use Nila\Payments\Models\Dtos\PurchaseDto;
    use Nila\Payments\Models\Dtos\WithdrawDto;
    use Nila\Payments\Models\Requests\DepositManualRequest;
    use Nila\Payments\Models\Requests\DepositRequest;
    use Nila\Payments\Models\Requests\PurchaseRequest;
    use Nila\Payments\Models\Requests\WithdrawManualRequest;
    use Nila\Payments\Models\Wallet;

    class PaymentsFactory {
        public static function manualDepositRequest( DepositDto $depositDto = null ) {
            if ( ! $depositDto ) {
                $depositDto = new DepositDto( Wallet::first(), 10000 );
            }

            return DepositManualRequest::create( [
                'wallet_id'      => $depositDto->wallet->id,
                'amount_request' => $depositDto->amount,
                'amount'         => $depositDto->amount,
                'gateway'        => $depositDto->gateway,
                'status'         => $depositDto->status,
            ] );
        }

        public static function depositRequest( DepositDto $depositDto = null ) {
            if ( ! $depositDto ) {
                $depositDto = new DepositDto( Wallet::first(), 10000 );
            }

            return DepositRequest::create( [
                'wallet_id'      => $depositDto->wallet->id,
                'amount_request' => $depositDto->amount,
                'amount'         => $depositDto->amount,
                'gateway'        => $depositDto->gateway,
                'status'         => $depositDto->status,
            ] );
        }

        public static function purchaseRequest( PurchaseDto $purchaseDto = null ) {
            if ( ! $purchaseDto ) {
                $purchaseDto = new PurchaseDto( Wallet::first(), 10000 );
            }

            return PurchaseRequest::create( [
                'wallet_id'      => $purchaseDto->wallet->id,
                'amount_request' => $purchaseDto->amount,
                'amount'         => $purchaseDto->amount,
                'gateway'        => $purchaseDto->gateway,
                'status'         => $purchaseDto->status,
            ] );
        }

        public static function withdrawRequest( WithdrawDto $withdrawDto = null ) {
            if ( ! $withdrawDto ) {
                $withdrawDto = new WithdrawDto( Wallet::first(), 10000 );
            }

            return WithdrawManualRequest::create( [
                'wallet_id'      => $withdrawDto->wallet->id,
                'amount_request' => $withdrawDto->amount,
                'amount'         => $withdrawDto->amount,
                'gateway'        => $withdrawDto->gateway,
                'status'         => $withdrawDto->status,
            ] );
        }


    }
