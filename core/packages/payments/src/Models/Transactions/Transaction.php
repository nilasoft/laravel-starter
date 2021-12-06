<?php

    namespace Nila\Payments\Models\Transactions;

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Nila\Payments\Models\Requests\TransactionRequest;
    use Nila\Payments\Models\Wallet;

    class Transaction extends Model {
        use HasFactory;

        protected $transaction_foreign_key = 'transaction_request_id';

        protected $table = 'transactions';
        protected $fillable = [
            'wallet_id',
            'amount',
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

        public function request() {
            return $this->belongsTo( TransactionRequest::class, $this->transaction_foreign_key );
        }

        public function wallet(): BelongsTo {
            return $this->belongsTo( Wallet::class );
        }

    }
