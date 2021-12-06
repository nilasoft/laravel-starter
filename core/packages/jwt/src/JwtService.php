<?php


    namespace Nila\Jwt;


    use JwtCacheEnum;
    use Nila\Jwt\Contracts\JwtContract;
    use Nila\Jwt\Exceptions\JwtErrorCode;
    use Nila\Jwt\Exceptions\JwtException;
    use Nila\Jwt\Models\Session;
    use Nila\Jwt\Provider\JwtProvider;
    use Illuminate\Contracts\Auth\Authenticatable;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Cache;
    use Lcobucci\JWT\UnencryptedToken;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    class JwtService implements JwtContract {
        private JwtProvider $mainProvider, $insideProvider;
        private array $configuration;
        private JwtProvider $mainInstance, $insideInstance;
        private object|null $session = null;

        public function __construct() {
            $this->configuration = config( 'jwt' );
            $this->mainProvider  = new JwtProvider( $this->getConfig( 'private_key' ) );
            $this->session();
        }

        public function setConfig( array $config ): self {
            if ( app()->runningUnitTests() ) {
                $this->configuration = $config;
            }

            return $this;
        }

        public function session( Session $session = null ): self {
            if ( $session ) {
                $this->session = $session;
            } else if ( $token = request()->bearerToken() ) {
                $session_id    = $this->mainProvider->decode( $token )->headers()->get( 'session_id', null );
                $cachedSession = Cache::rememberForever( JwtCacheEnum::SESSION . $session_id,
                    function() use ( $session_id ) {
                        return Session::find( $session_id )?->getForCache();
                    } );
                if ( $cachedSession ) {
                    $this->session = (object) $cachedSession;
                } else {
                    throw new JwtException( 'Token expired!', JwtErrorCode::TOKEN_EXPIRED,
                        ResponseAlias::HTTP_FORBIDDEN );
                }
            }
            if ( $secret = $this->session?->secret ) {
                $this->insideProvider = new JwtProvider( $secret, true );
            }

            return $this;
        }

        private function getConfig( string $key ) {
            return Arr::get( $this->configuration, $key );
        }

        public function extract( string $token ): UnencryptedToken {
            return $this->mainProvider->decode( $token );
        }

        public function getInsideToken( string $token ): UnencryptedToken {
            $this->assertInsideToken( $token );
            $token       = $this->mainProvider->decode( $token );
            $insideToken = $token->claims()->get( '_token' );

            return $this->insideProvider->decode( $insideToken );
        }

        public function create( Authenticatable $user ): self {
            try {
                $this->mainInstance = $this->mainProvider->encode()
                                                         ->expiresAt( $this->getConfig( 'expired_at' ) )
                                                         ->header( 'session_id', $this->session->id )
                                                         ->header( 'user_version', $userVersion = $user->getVersion() )
                                                         ->header( 'role_id', ( $role = $user->roles()->first() )->id )
                                                         ->header( 'role_version', $role->getVersion() );

                $this->insideInstance = $this->insideProvider->encode()
                                                             ->claim( 'role', $role->only( 'id', 'name' ) )
                                                             ->claim( 'permissions', $user->getAllPermissions()
                                                                                          ->pluck( 'name', 'id' )
                                                                                          ->toArray() )
                                                             ->claim( 'user',
                                                                 array_merge( $user->only( 'id', 'name', 'email' ), [
                                                                     'version' => $userVersion
                                                                 ] ) );
            } catch ( \Throwable $e ) {
                throw new JwtException( 'Creating the token failed!', JwtErrorCode::SESSION_NOT_FOUND,
                    ResponseAlias::HTTP_FORBIDDEN );
            }

            return $this;
        }

        public function claim( string $key, string|int|array $value ): self {
            $this->insideInstance = $this->insideInstance->claim( $key, $value );

            return $this;
        }

        public function header( string $key, string|int|array $value ): self {
            $this->insideInstance = $this->insideInstance->header( $key, $value );

            return $this;
        }

        public function accessToken(): string {
            $this->mainInstance->claim( '_token', $this->insideInstance->getToken() );

            return $this->mainInstance->getToken();
        }

        public function validate( string $token ): bool {
            return $this->mainProvider->validate( $token );
        }

        public function assert( string $token ): void {
            $this->mainProvider->assert( $token );
        }

        public function createRefreshToken( Authenticatable $user ): self {
            try {
                $this->mainInstance   = $this->mainProvider->encode()
                                                           ->expiresAt( $this->getConfig( 'refreshExpired_at' ) )
                                                           ->header( 'refresh', true )
                                                           ->header( 'session_id', $this->session->id );
                $this->insideInstance = $this->insideProvider->encode()
                                                             ->claim( 'user_id', $user->id )
                                                             ->claim( 'email', $user->email );
            } catch ( \Throwable $e ) {
                throw new JwtException( 'Creating the refresh token failed!', JwtErrorCode::SESSION_NOT_FOUND,
                    ResponseAlias::HTTP_FORBIDDEN );
            }

            return $this;
        }

        public function refreshToken(): string {
            $this->mainInstance->claim( '_token', $this->insideInstance->getToken() );

            return $this->mainInstance->getToken();
        }

        public function getPermissions( string $token ): array {
            return $this->getInsideToken( $token )->claims()->get( 'permissions' );
        }

        public function validateInsideToken( string $token ): bool {
            $token       = $this->mainProvider->decode( $token );
            $insideToken = $token->claims()->get( '_token' );

            return $this->insideProvider->validate( $insideToken );
        }

        public function assertInsideToken( string $token ): void {
            $token       = $this->mainProvider->decode( $token );
            $insideToken = $token->claims()->get( '_token' );
            $this->insideProvider->assert( $insideToken );
        }
    }
