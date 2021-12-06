<?php

    namespace App\Models;

    use App\JsonApi\Filters\LikeFilter;
    use App\Models\Traits\CacheControlTrait;
    use App\Notifications\ResetPasswordEmail;
    use Illuminate\Auth\Notifications\VerifyEmail;
    use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
    use Nila\Jwt\Traits\JwtTokenCan;
    use Nila\Payments\Jobs\CreateWalletTrait;
    use Nila\Payments\Models\Traits\HasWallet;
    use Nila\Resources\Traits\ResourcesRelationship;
    use Nila\Jwt\Models\Session;
    use Nila\Permissions\Models\Traits\HasRelations;
    use Nila\Permissions\HasRoles;
    use Illuminate\Contracts\Auth\MustVerifyEmail;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Spatie\Activitylog\LogOptions;
    use Spatie\Activitylog\Traits\LogsActivity;

    /**
 * @mixin IdeHelperUser
 */
    class User extends Authenticatable implements MustVerifyEmail, AuthenticatableContract {
        use HasFactory, Notifiable, LogsActivity;
        use HasRoles, HasRelations, ResourcesRelationship, HasWallet, JwtTokenCan;
        use CreateWalletTrait {
            CreateWalletTrait::booted as private handleWallet;
        }
        use CacheControlTrait {
            CacheControlTrait::booted as private handleUpdatingCache;
        }

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name',
            'email',
            'password',
            'tokens',
            'version'
        ];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $hidden = [
            'password',
        ];

        /**
         * The attributes that should be cast to native types.
         *
         * @var array
         */
        protected $casts = [
            'email_verified_at' => 'datetime',
            'tokens'            => 'array',
            'version'           => 'integer'
        ];

        protected static function booted() {
            self::handleWallet();
            self::handleUpdatingCache();
        }

        /**
         * JsonApi like filter
         *
         * @param Builder $query
         * @param         $name
         * @param         $value
         *
         * @return Builder
         */
        public function scopeFilterLike( Builder $query, $name, $value ): Builder {
            return LikeFilter::make( $name )->apply( $query, $value );
        }

        public function getActivitylogOptions(): LogOptions {
            return LogOptions::defaults()->logFillable();
        }

        /**
         * Access the user's sessions through account
         *
         * @return HasMany
         */
        public function sessions(): HasMany {
            return $this->hasMany( Session::class );
        }

        public function posts(): HasMany {
            return $this->hasMany( Post::class );
        }

        public function getVersion(): int {
            return $this->version;
        }

        public function increaseVersion(): bool {
            try {
                $this->forceFill( [ 'version' => $this->getVersion() + 1 ] );
                $this->saveQuietly();
            } catch ( \Throwable $e ) {
                return false;
            }

            return true;
        }

        public function getDeviceLimit(): int {
            return 2;
        }

        /**
         * Send the password reset notification.
         *
         * @param string $token
         *
         * @return void
         */
        public function sendPasswordResetNotification( $token ) {
            $this->notify( new ResetPasswordEmail( $token ) );
        }

        /**
         * Send the email verification notification.
         *
         * @return void
         */
        public function sendEmailVerificationNotification() {
            $this->notify( new VerifyEmail );
        }
    }
