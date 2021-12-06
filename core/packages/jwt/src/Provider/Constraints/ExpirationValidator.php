<?php

    namespace Nila\Jwt\Provider\Constraints;

    use DateTimeImmutable;
    use Lcobucci\JWT\Token;
    use Lcobucci\JWT\Validation\Constraint;
    use Nila\Jwt\Exceptions\JwtErrorCode;
    use Nila\Jwt\Exceptions\JwtException;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    final class ExpirationValidator implements Constraint {

        /**
         * @throws JwtException
         */
        public function assert( Token $token ): void {
            $diff = ( new DateTimeImmutable( 'UTC' ) )->diff( $token->claims()->get( 'exp' ) );
            if ( '-' == $diff->format( '%R' ) ) {
                throw new JwtException( 'Token expired!', JwtErrorCode::TOKEN_EXPIRED, ResponseAlias::HTTP_FORBIDDEN );
            }
        }
    }
