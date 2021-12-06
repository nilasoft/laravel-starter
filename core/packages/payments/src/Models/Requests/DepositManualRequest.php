<?php

    namespace Nila\Payments\Models\Requests;

    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Nila\Payments\Models\Transactions\Deposit;
    use Nila\Resources\Traits\ResourcesRelationship;
    use TransactionRequestsEnum;

    class DepositManualRequest extends TransactionRequest {
        use ResourcesRelationship;

        protected static function type(): string {
            return TransactionRequestsEnum::DEPOSIT_MANUAL;
        }

        public function receipt() {
            return $this->uploads->first()->url;
        }

        public function transactions(): HasOne {
            return $this->hasOne( Deposit::class, $this->transaction_foreign_key );
        }
    }
