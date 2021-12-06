<?php

    namespace App\Http\Controllers\Auth;

    use App\Http\Controllers\Controller;
    use App\Models\User;
    use DateTimeImmutable;
    use DateTimeZone;
    use Nila\Jwt\Contracts\JwtContract;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\ValidationException;

    class RefreshTokenController extends Controller {
        /**
         * @throws ValidationException
         */
        public function __invoke( Request $request, JwtContract $jwt ): JsonResponse {
            $this->validationRequest( $request );

            $token = $jwt->extract( $request->bearerToken() );
            if ( ! $token->headers()->get( 'refresh', false ) ) {
                throw ValidationException::withMessages( [
                    'token' => 'Please send a refresh token!'
                ] );
            }
            $diff = ( new DateTimeImmutable( 'UTC' ) )->diff( $token->claims()->get( 'exp' ) );
            if ( '-' == $diff->format( '%R' ) ) {
                throw ValidationException::withMessages( [
                    'refresh_token' => 'Refresh token expired!'
                ] );
            }
            $insideToken = $jwt->getInsideToken( $request->bearerToken() );
            $credentials = [
                'id'    => $insideToken->claims()->get( 'user_id' ),
                'email' => $insideToken->claims()->get( 'email' ),
            ];
            $user        = User::whereId( $credentials[ 'id' ] )->whereEmail( $credentials[ 'email' ] )->first();
            if ( $user ) {
                return new JsonResponse( [
                    'access_token'  => $jwt->create( $user )->accessToken(),
                    'refresh_token' => $jwt->createRefreshToken( $user )->refreshToken(),
                    'user'          => $user->only( 'name', 'email' )
                ], 200 );
            }
            throw ValidationException::withMessages( [
                'refresh_token' => 'Refresh token is invalid!'
            ] );
        }

        private function validationRequest( Request $request ) {
            Validator::make( array_merge( $request->all(), [
                'token' => $request->bearerToken()
            ] ), [
                'token'                => [ 'required', 'string' ],
                'g-recaptcha-response' => env( 'RECAPTCHAV3_ENABLE',
                    false ) ? 'required|recaptchav3:login,0.5' : 'nullable'
            ] )->validate();
        }
    }
