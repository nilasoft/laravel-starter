<?php

    namespace Nila\Payments\Models\Transactions;

    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Nila\Payments\Models\Requests\WithdrawManualRequest;
    use TransactionsEnum;

    class Withdraw extends Transaction {

        protected static function type(): string {
            return TransactionsEnum::WITHDRAW;
        }

        public function request(): BelongsTo {
            return $this->belongsTo( WithdrawManualRequest::class, $this->transaction_foreign_key );
        }
    }
