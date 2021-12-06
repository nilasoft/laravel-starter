<?php

    namespace App\Exceptions;

    use Exception;
    use LaravelJsonApi\Core\Document\Error;
    use LaravelJsonApi\Core\Responses\ErrorResponse;
    use Throwable;

    class BaseException extends Exception {
        private string $msg;
        private int $errorCode, $responseCode;

        public function __construct( string $message = "", int $ErrorCode = 0, int $responseCode = 500, Throwable $previous = null ) {
            parent::__construct( $message, $responseCode, $previous );
            $this->msg          = $message;
            $this->errorCode    = $ErrorCode;
            $this->responseCode = $responseCode;
        }

        public static function make( string $message = "", int $ErrorCode = 0, int $responseCode = 500, Throwable $previous = null ): static {
            return ( new static( $message, $ErrorCode, $responseCode, $previous ) );
        }

        /**
         * Render the exception as an HTTP response.
         *
         * @return ErrorResponse
         */
        public function render(): ErrorResponse {
            $error = Error::make()
                          ->setCode( $this->errorCode )
                          ->setStatus( $this->responseCode )
                          ->setTitle( 'Unexpected error!' )
                          ->setDetail( $this->msg );

            return ErrorResponse::make( $error );
        }
    }
