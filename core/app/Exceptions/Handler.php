<?php

    namespace App\Exceptions;

    use Illuminate\Auth\Access\AuthorizationException;
    use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
    use LaravelJsonApi\Exceptions\ExceptionParser;
    use Nila\Jwt\Exceptions\JwtException;
    use Nila\Menus\Exceptions\MenusException;
    use Nila\Payments\Exceptions\PaymentsGatewayException;
    use Nila\Payments\Exceptions\PaymentsException;
    use Nila\Permissions\Exceptions\PermissionsException;
    use Nila\Resources\Exceptions\ResourcesException;
    use Throwable;

    class Handler extends ExceptionHandler {
        /**
         * A list of the exception types that are not reported.
         *
         * @var array
         */
        protected $dontReport = [//
        ];

        /**
         * A list of the inputs that are never flashed for validation exceptions.
         *
         * @var array
         */
        protected $dontFlash = [
            'current_password',
            'password',
            'password_confirmation',
        ];

        /**
         * Register the exception handling callbacks for the application.
         *
         * @return void
         */
        public function register() {
            $this->renderable( ExceptionParser::make()->renderable() );
        }

        public function render( $request, Throwable $e ) {
            switch ( true ) {
                case $e instanceof AuthorizationException:
                    throw self::throw( $e );
                case $e instanceof JwtException:
                    throw self::throw( $e );
                case $e instanceof MenusException:
                    throw self::throw( $e );
                case $e instanceof PaymentsException:
                    throw self::throw( $e );
                case $e instanceof PermissionsException:
                    throw self::throw( $e );
                case $e instanceof ResourcesException:
                    throw self::throw( $e );
                case $e instanceof PaymentsGatewayException:
                    throw new GatewayException( $e );
            }

            return parent::render( $request, $e );
        }

        private static function throw( Throwable $e ): \Exception {
            if ( method_exists( $e, 'getErrorCode' ) ) {
                $errorCode = $e->getErrorCode();
            } else {
                $errorCode = 9999;
            }

            return BaseException::make( $e->getMessage(), $errorCode, $e->getCode() );
        }

    }
