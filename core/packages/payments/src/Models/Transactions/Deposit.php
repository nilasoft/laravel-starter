<?php

    namespace Nila\Payments\Models\Transactions;

    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Nila\Payments\Models\Requests\DepositManualRequest;
    use Nila\Payments\Models\Requests\DepositRequest;
    use TransactionsEnum;

    class Deposit extends Transaction {

        protected static function type(): string {
            return TransactionsEnum::DEPOSIT;
        }

        public function deoisitRequest(): BelongsTo {
            return $this->belongsTo( DepositRequest::class, $this->transaction_foreign_key );
        }

        public function depositManualRequest(): BelongsTo {
            return $this->belongsTo( DepositManualRequest::class, $this->transaction_foreign_key );
        }

        public function request() {
            return $this->depositManualRequest ? : $this->deoisitRequest;
        }
    }
