<?php


    namespace Nila\Payments\Models\Requests;


    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Nila\Payments\Models\Transactions\Withdraw;
    use Nila\Resources\Traits\ResourcesRelationship;
    use TransactionRequestsEnum;

    class WithdrawManualRequest extends TransactionRequest {

        use ResourcesRelationship;

        protected static function type(): string {
            return TransactionRequestsEnum::WITHDRAW_MANUAL;
        }

        public function receipt() {
            return $this->uploads->first()->url;
        }

        public function account(): BelongsTo {
            return $this->belongsTo( BankAccount::class );
        }

        public function transactions(): HasOne {
            return $this->hasOne( Withdraw::class, $this->transaction_foreign_key );
        }
    }
