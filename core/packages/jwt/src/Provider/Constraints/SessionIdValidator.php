<?php

    namespace Nila\Jwt\Provider\Constraints;

    use JwtCacheEnum;
    use Nila\Jwt\Exceptions\JwtErrorCode;
    use Nila\Jwt\Exceptions\JwtException;
    use Nila\Jwt\Models\Session;
    use Illuminate\Support\Facades\Cache;
    use Lcobucci\JWT\Token;
    use Lcobucci\JWT\Validation\Constraint;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    final class SessionIdValidator implements Constraint {

        public function assert( Token $token ): void {
            $session_id   = $token->headers()->get( 'session_id', false );
            $user_version = $token->headers()->get( 'user_version', false );

            if ( ! $session_id ) {
                throw new JwtException( 'Session id not found in header!', JwtErrorCode::SESSION_NOT_FOUND,
                    ResponseAlias::HTTP_FORBIDDEN );
            }
            if ( ! $user_version ) {
                throw new JwtException( 'User\'s version not found in header!', JwtErrorCode::USERS_VERSION_NOT_FOUND,
                    ResponseAlias::HTTP_FORBIDDEN );
            }
            $session = Cache::rememberForever( JwtCacheEnum::SESSION . $session_id, function() use ( $session_id ) {
                return Session::find( $session_id )->getForCache();
            } );

            if ( $session[ 'userVersion' ] != $user_version ) {
                throw new JwtException( 'User\'s token is out-of-date!', JwtErrorCode::TOKEN_IS_OUT_OF_DATE,
                    ResponseAlias::HTTP_FORBIDDEN );
            }
        }
    }
