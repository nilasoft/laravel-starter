<?php

    namespace App\Exceptions;

    use Exception;
    use Nila\Payments\Exceptions\PaymentsGatewayException;

    class GatewayException extends Exception {
        private PaymentsGatewayException $exception;

        public function __construct( PaymentsGatewayException $exception ) {
            $this->exception = $exception;
            parent::__construct( $exception->getMessage(), $exception->getErrorCode(), $exception->getPrevious() );
        }

        public function render() {
            return view( 'exceptions.gateways.error', [ 'error' => $this->exception->getMessage() ] );
        }


    }
