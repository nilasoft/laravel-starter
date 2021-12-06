<?php


    namespace Nila\Payments\Models\Requests;


    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Nila\Payments\Models\Transactions\Deposit;
    use TransactionRequestsEnum;

    class DepositRequest extends TransactionRequest {

        protected static function type(): string {
            return TransactionRequestsEnum::DEPOSIT;
        }

        public function transactions(): HasOne {
            return $this->hasOne( Deposit::class, $this->transaction_foreign_key );
        }
    }
