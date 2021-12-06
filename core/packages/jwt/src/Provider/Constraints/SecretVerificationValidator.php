<?php

    namespace Nila\Jwt\Provider\Constraints;

    use Lcobucci\JWT\Signer;
    use Lcobucci\JWT\Token;
    use Lcobucci\JWT\Validation\Constraint;
    use Nila\Jwt\Exceptions\JwtErrorCode;
    use Nila\Jwt\Exceptions\JwtException;
    use Symfony\Component\HttpFoundation\Response;

    final class SecretVerificationValidator implements Constraint {
        private Signer $signer;
        private Signer\Key $key;

        public function __construct( Signer $signer, Signer\Key $key ) {
            $this->signer = $signer;
            $this->key    = $key;
        }

        public function assert( Token $token ): void {
            if ( $token->headers()->get( 'alg' ) !== $this->signer->algorithmId() ) {
                throw new JwtException( 'Token signer mismatch!', JwtErrorCode::TOKEN_MISMATCH,
                    Response::HTTP_FORBIDDEN );
            }

            if ( ! $this->signer->verify( $token->signature()->hash(), $token->payload(), $this->key ) ) {
                throw new JwtException( 'Token signature mismatch!', JwtErrorCode::TOKEN_MISMATCH,
                    Response::HTTP_FORBIDDEN );
            }
        }
    }
