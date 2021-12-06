<?php


    namespace Nila\Jwt\Exceptions;


    use Exception;
    use Throwable;

    class JwtException extends Exception {
        private int $errorCode;

        public function __construct( string $message, int $errorCode, int $code = 0, Throwable $previous = null ) {
            parent::__construct( $message, $code, $previous );
            $this->errorCode = $errorCode;
        }

        public function getErrorCode(): int {
            return $this->errorCode;
        }
    }
