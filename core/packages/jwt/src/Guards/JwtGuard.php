<?php


    namespace Nila\Jwt\Guards;


    use Illuminate\Contracts\Auth\Guard;
    use Nila\Jwt\Contracts\JwtContract;
    use Illuminate\Auth\GuardHelpers;
    use Illuminate\Contracts\Auth\Authenticatable;
    use Illuminate\Contracts\Auth\UserProvider;
    use Illuminate\Http\Request;

    class JwtGuard implements Authenticatable, Guard {
        use GuardHelpers;

        private Request $request;
        private JwtContract $jwtProvider;

        public function __construct( UserProvider $provider, Request $request, JwtContract $jwt ) {
            $this->provider    = $provider;
            $this->jwtProvider = $jwt;
            $this->user        = null;
            $token             = $request->bearerToken();
            if ( $token ) {
                if ( ! $this->jwtProvider->extract( $token )->headers()->get( 'refresh', false ) ) {
                    $this->jwtProvider->assert( $token );
                    $this->user = $this->provider->retrieveByCredentials( $this->jwtProvider->getInsideToken( $token )
                                                                              ->claims()
                                                                              ->get( 'user' ) );
                }
            }
        }

        public function getAuthIdentifierName(): string {
            return 'id';
        }

        public function getAuthIdentifier() {
            return $this->user->id;
        }

        public function getAuthPassword(): string {
            return $this->user->password;
        }

        public function getRememberToken(): void {
            // TODO: Implement getRememberToken() method.
        }

        public function setRememberToken( $value ): void {
            // TODO: Implement setRememberToken() method.
        }

        public function getRememberTokenName(): void {
            // TODO: Implement getRememberTokenName() method.
        }

        public function user() {
            return $this->user;
        }

        public function attempt( array $credentials, ?bool $remember = false ): bool {
            $user = $this->provider->retrieveByCredentials( $credentials );
            if ( $this->provider->validateCredentials( $user, $credentials ) ) {
                return true;
            }

            return false;
        }

        public function check(): bool {
            if ( isset( $this->user ) ) {
                return true;
            }

            return false;
        }

        public function loginUsingId( int $id ) {
            return $this->user = $this->provider->retrieveById( $id );
        }

        /**
         * Validate a user's credentials.
         *
         * @param array $credentials
         *
         * @return bool
         */
        public function validate( array $credentials = [] ) {
            if ( $this->provider->retrieveByCredentials( $credentials ) ) {
                return true;
            }

            return false;
        }
    }
