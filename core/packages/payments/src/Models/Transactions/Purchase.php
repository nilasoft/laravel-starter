<?php

    namespace Nila\Payments\Models\Transactions;

    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Nila\Payments\Models\Requests\PurchaseRequest;
    use TransactionsEnum;

    class Purchase extends Transaction {

        protected static function type(): string {
            return TransactionsEnum::PURCHASE;
        }

        public function request(): BelongsTo {
            return $this->belongsTo( PurchaseRequest::class, $this->transaction_foreign_key );
        }
    }
