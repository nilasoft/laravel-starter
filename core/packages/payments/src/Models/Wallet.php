<?php

    namespace Nila\Payments\Models;

    use App\Models\User;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Support\Facades\Log;
    use Nila\Payments\Exceptions\PaymentsErrorCode;
    use Nila\Payments\Exceptions\PaymentsException;
    use Nila\Payments\Models\Requests\BankAccount;
    use Nila\Payments\Models\Requests\WithdrawManualRequest;
    use Nila\Payments\Models\Transactions\Deposit;
    use Nila\Payments\Models\Transactions\Purchase;
    use Nila\Payments\Models\Transactions\Withdraw;
    use PaymentsStatusEnum;

    class Wallet extends Model {
        use HasFactory;

        protected $fillable = [
            'balance',
            'active',
            'currency',
            'version'
        ];

        protected $casts = [
            'active'  => 'boolean',
            'version' => 'integer'
        ];

        /**
         * Perform any actions required after the model boots.
         *
         * @return void
         */
        protected static function booted() {
            self::saved( function( self $model ) {
                $model->increaseVersion();
            } );
        }


        public function getBalance() {
            return $this->balance - $this->getBalanceLock();
        }

        public function getBalanceLock(): int {
            return intval( $this->withdrawRequests()
                                ->whereStatus( PaymentsStatusEnum::PENDING )
                                ->sum( 'amount_request' ) );
        }

        public function hasPendingWithdrawRequest(): bool {
            return $this->withdrawRequests()->whereStatus( PaymentsStatusEnum::PENDING )->exists();
        }

        public function getGetBalanceAttribute() {
            return number_format( $this->balance ) . ' ' . $this->currency;
        }

        public function user(): BelongsTo {
            return $this->belongsTo( User::class );
        }

        public function bankAccount(): HasMany {
            return $this->hasMany( BankAccount::class );
        }

        public function deposits(): HasMany {
            return $this->hasMany( Deposit::class );
        }

        public function withdraws(): HasMany {
            return $this->hasMany( Withdraw::class );
        }

        public function withdrawRequests(): HasMany {
            return $this->hasMany( WithdrawManualRequest::class );
        }

        public function purchases(): HasMany {
            return $this->hasMany( Purchase::class );
        }

        /**
         * Save the model to the database.
         *
         * @param array $options
         *
         * @return bool
         * @throws PaymentsException
         */
        public function save( array $options = [] ) {
            $this->mergeAttributesFromClassCasts();

            $query = $this->newModelQuery();

            // If the "saving" event returns false we'll bail out of the save and return
            // false, indicating that the save failed. This provides a chance for any
            // listeners to cancel save operations if validations fail or whatever.
            if ( $this->fireModelEvent( 'saving' ) === false ) {
                return false;
            }

            // If the model already exists in the database we can just update our record
            // that is already in this database using the current IDs in this "where"
            // clause to only update this model. Otherwise, we'll just insert them.
            if ( $this->exists ) {
                if ( $this->getVersion() == self::find( $this->id )?->getVersion() ) {
                    $saved = $this->isDirty() ? $this->performUpdate( $query ) : true;
                } else {
                    throw new PaymentsException( 'Instance version is not the latest!',
                        PaymentsErrorCode::INSTANCE_VERSION_IS_OUT_OF_DATE );
                }
            }

            // If the model is brand new, we'll insert it into our database and set the
            // ID attribute on the model to the value of the newly inserted row's ID
            // which is typically an auto-increment value managed by the database.
            else {
                $saved = $this->performInsert( $query );

                if ( ! $this->getConnectionName() && $connection = $query->getConnection() ) {
                    $this->setConnection( $connection->getName() );
                }
            }

            // If the model is successfully saved, we need to do a few more things once
            // that is done. We will call the "saved" method here to run any actions
            // we need to happen after a model gets successfully saved right here.
            if ( $saved ) {
                $this->finishSave( $options );
            }

            return $saved;
        }

        protected function getVersion(): int {
            return $this->version;
        }

        protected function increaseVersion(): bool|int {
            return $this->increment( 'version' );
        }
    }
