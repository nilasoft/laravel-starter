<?php

    namespace Nila\Payments\Models\Requests;

    use Carbon\Carbon;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Prunable;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Support\Facades\DB;
    use Nila\Payments\Exceptions\PaymentsErrorCode;
    use Nila\Payments\Exceptions\PaymentsException;
    use Nila\Payments\Models\Transactions\Transaction;
    use Nila\Payments\Models\Wallet;
    use Nila\Resources\Contracts\ResourcesContract;
    use PaymentsStatusEnum;

    class TransactionRequest extends Model {
        use HasFactory, Prunable;

        protected $transaction_foreign_key = 'transaction_request_id';

        protected $table = 'transaction_requests';
        protected $fillable = [
            'wallet_id',
            'amount_request',
            'amount',
            'gateway',
            'status',
            'extra'
        ];
        protected $casts = [
            'extra' => 'array'
        ];

        protected static function booted() {
            static::addGlobalScope( 'wrapper', static::scope() );
            static::creating( function( self $model ) {
                $model->forceFill( [ 'type' => static::type() ] );
            } );
        }

        protected static function scope(): callable {
            return function( Builder $builder ) {
                if ( static::type() === 'no-scope' ) {
                    return $builder;
                }

                return $builder->whereType( static::type() );
            };
        }

        protected static function type(): string {
            return 'no-scope';
        }

        public function transactions() {
            return $this->hasOne( Transaction::class, $this->transaction_foreign_key );
        }

        public function setStatusAttribute( $value ) {
            if ( ! in_array( $this->status, [ PaymentsStatusEnum::APPROVED, PaymentsStatusEnum::FAILED ] ) ) {
                $this->attributes[ 'status' ] = $value;
            }
        }

        public function updateStatus( string $status ): bool {
            try {
                DB::beginTransaction();
                $result = $this->update( [ 'status' => $status ] );
                DB::commit();
            } catch ( \Throwable $e ) {
                DB::rollBack();
                throw new PaymentsException( 'Failed to update the status!', PaymentsErrorCode::STATUS_UPDATE_FAILED );
            }

            return $result ?? false;
        }

        public function isNotVerified(): bool {
            return ! $this->isVerified();
        }

        public function isVerified(): bool {
            return $this->status == PaymentsStatusEnum::APPROVED;
        }

        public function scopeJustVerified( Builder $builder ) {
            return $builder->whereStatus( PaymentsStatusEnum::APPROVED );
        }

        public function scopeJustUnVerified( Builder $builder ) {
            return $builder->where( 'status', '<>', PaymentsStatusEnum::APPROVED );
        }


        public function wallet(): BelongsTo {
            return $this->belongsTo( Wallet::class );
        }


        /**
         * Get the prunable model query.
         *
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function prunable() {
            return static::where( [
                'status' => PaymentsStatusEnum::PENDING,
                [
                    'created_at',
                    '<',
                    Carbon::now()->modify( '-' . ltrim( config( 'payments.expiration' ), '+ -' ) )
                ]
            ] );
        }

        /**
         * Prepare the model for pruning.
         *
         * @return void
         */
        protected function pruning() {
            if ( in_array( $this->type, [ 'withdraw_manual', 'deposit_manual' ] ) ) {
                if ( method_exists( $this, 'removeUploads' ) ) {
                    $this->removeUploads();
                }
            }
        }
    }
