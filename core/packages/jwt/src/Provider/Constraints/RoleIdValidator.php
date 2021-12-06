<?php

    namespace Nila\Jwt\Provider\Constraints;

    use Illuminate\Support\Facades\App;
    use JwtCacheEnum;
    use Nila\Jwt\Exceptions\JwtErrorCode;
    use Nila\Jwt\Exceptions\JwtException;
    use Illuminate\Support\Facades\Cache;
    use Lcobucci\JWT\Token;
    use Lcobucci\JWT\Validation\Constraint;
    use Nila\Permissions\Contracts\PermissionsContract;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    final class RoleIdValidator implements Constraint {

        /**
         * @throws JwtException
         */
        public function assert( Token $token ): void {
            $role_id      = $token->headers()->get( 'role_id', false );
            $role_version = $token->headers()->get( 'role_version', false );

            if ( ! $role_id ) {
                throw new JwtException( 'Role id not found in header!', JwtErrorCode::ROLE_NOT_FOUND,
                    ResponseAlias::HTTP_FORBIDDEN );
            }
            if ( ! $role_version ) {
                throw new JwtException( 'Role\'s version not found in header!', JwtErrorCode::ROLE_VERSION_NOT_FOUND,
                    ResponseAlias::HTTP_FORBIDDEN );
            }
            $role = Cache::rememberForever( JwtCacheEnum::ROLE . $role_id, function() use ( $role_id ) {
                return App::make( PermissionsContract::class )->findRole( $role_id );
            } );

            if ( $role?->getVersion() != $role_version ) {
                // TODO: refresh token process
                throw new JwtException( 'User\'s token is out-of-date!', JwtErrorCode::TOKEN_IS_OUT_OF_DATE,
                    ResponseAlias::HTTP_FORBIDDEN );
            }
        }
    }
