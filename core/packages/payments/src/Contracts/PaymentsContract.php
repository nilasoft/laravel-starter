<?php

    namespace Nila\Payments\Contracts;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\RedirectResponse;
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

    interface PaymentsContract {
        function deposit( string $gateway, int $amount, Wallet $wallet ): RedirectResponse;

        function depositAndPurchase( string $gateway, int $amount, Wallet $wallet, Model $model = null ): RedirectResponse;

        function purchase( PurchaseDto $purchaseDto, Model $model = null ): Purchase;

        function verify( string $model, int $id ): Deposit|Purchase;

        function depositFailed( DepositRequest|PurchaseRequest $model ): DepositRequest|PurchaseRequest;

        function depositManual( DepositDto $depositDto, string $field ): DepositManualRequest;

        function depositManualApprove( DepositManualRequest $depositManualRequest, int $amount ): Deposit;

        function depositManualReject( DepositManualRequest $depositManualRequest ): DepositManualRequest;

        function withdrawManual( WithdrawDto $withdrawDto ): WithdrawManualRequest;

        function withdrawManualApprove( WithdrawManualRequest $withdrawManualRequest, int $amount ): Withdraw;

        function withdrawManualReject( WithdrawManualRequest $withdrawManualRequest ): WithdrawManualRequest;

    }
