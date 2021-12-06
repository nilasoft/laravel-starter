<?php


    namespace Nila\Payments\Models\Requests;


    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Nila\Payments\Models\Transactions\Deposit;
    use Nila\Payments\Models\Transactions\Purchase;
    use TransactionRequestsEnum;

    class PurchaseRequest extends TransactionRequest {

        protected static function type(): string {
            return TransactionRequestsEnum::PURCHASE;
        }

        public function purchasable() {
            return $this->morphTo();
        }

        public function associated() {
            return $this->purchasable;
        }

        public function purchaseTransaction(): HasOne {
            return $this->hasOne( Purchase::class, $this->transaction_foreign_key );
        }

        public function depositTransaction(): HasOne {
            return $this->hasOne( Deposit::class, $this->transaction_foreign_key );
        }

        public function transactions() {
            return $this->purchaseTransaction ? : $this->depositTransaction;
        }
    }
