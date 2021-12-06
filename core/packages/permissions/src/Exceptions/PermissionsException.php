<?php


    namespace Nila\Permissions\Exceptions;


    use Exception;
    use Throwable;

    class PermissionsException extends Exception {
        private int $errorCode;

        public function __construct( string $message, int $errorCode, $code = 0, Throwable $previous = null ) {
            parent::__construct( $message, $code, $previous );
            $this->errorCode = $errorCode;
        }

        public function getErrorCode() {
            return $this->errorCode;
        }
    }
