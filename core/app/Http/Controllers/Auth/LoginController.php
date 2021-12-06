<?php

    namespace App\Http\Controllers\Auth;

    use App\Http\Controllers\Controller;
    use App\Models\User;
    use App\Providers\RouteServiceProvider;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Response;
    use Illuminate\Validation\ValidationException;
    use Nila\Jwt\Contracts\JwtContract;
    use Illuminate\Contracts\Auth\Authenticatable;
    use Illuminate\Foundation\Auth\AuthenticatesUsers;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Laravel\Socialite\Facades\Socialite;

    class LoginController extends Controller {
        private JwtContract $jwt;
        /*
        |--------------------------------------------------------------------------
        | Login Controller
        |--------------------------------------------------------------------------
        |
        | This controller handles authenticating users for the application and
        | redirecting them to your home screen. The controller uses a trait
        | to conveniently provide its functionality to your applications.
        |
        */

        use AuthenticatesUsers;

        /**
         * PHP 5 allows developers to declare constructor methods for classes.
         * Classes which have a constructor method call this method on each newly-created object,
         * so it is suitable for any initialization that the object may need before it is used.
         *
         * Note: Parent constructors are not called implicitly if the child class defines a constructor.
         * In order to run a parent constructor, a call to parent::__construct() within the child constructor is
         * required.
         *
         * param [ mixed $args [, $... ]]
         * @link https://php.net/manual/en/language.oop5.decon.php
         */
        public function __construct( JwtContract $jwt ) {
            $this->jwt = $jwt;
        }


        /**
         * Where to redirect users after login.
         *
         * @var string
         */
        protected $redirectTo = RouteServiceProvider::HOME;

        /**
         * Validate the user login request.
         *
         * @param Request $request
         *
         * @return void
         *
         */
        protected function validateLogin( Request $request ) {
            $request->validate( [
                $this->username()      => 'required|string|max:512',
                'password'             => 'required|string|min:8',
                'g-recaptcha-response' => env( 'RECAPTCHAV3_ENABLE',
                    false ) ? 'required|recaptchav3:login,0.5' : 'nullable'
            ] );
        }

        /**
         * Send the response after the user was authenticated.
         *
         * @param Request $request
         *
         * @return RedirectResponse|JsonResponse
         */
        protected function sendLoginResponse( Request $request ): JsonResponse|RedirectResponse {
            $this->clearLoginAttempts( $request );

            if ( $response = $this->authenticated( $request, Auth::user() ) ) {
                return $response;
            }

            return $request->wantsJson() ? new JsonResponse( [], 204 ) : redirect()->intended( $this->redirectPath() );
        }

        /**
         * The user has been authenticated.
         *
         * @param Request $request
         * @param User    $user
         *
         * @return JsonResponse|RedirectResponse
         */
        protected function authenticated( Request $request, Authenticatable $user ): JsonResponse|RedirectResponse {
            // capture user session
            $session = capture_session();

            return $request->wantsJson() ? new JsonResponse( [
                'access_token'  => $this->jwt->session( $session )->create( $user )->accessToken(),
                'refresh_token' => $this->jwt->createRefreshToken( $user )->refreshToken(),
                'user'          => $user->only( 'name', 'email' )
            ], 200 ) : redirect()->intended( $this->redirectPath() );
        }


        /**
         * @return \Symfony\Component\HttpFoundation\RedirectResponse
         */
        public function google(): \Symfony\Component\HttpFoundation\RedirectResponse {
            return Socialite::driver( 'google' )->redirect();
        }

        /**
         * @param Request $request
         *
         * @return JsonResponse
         */
        public function googleCallback( Request $request ): JsonResponse {
            $googleUser = Socialite::driver( 'google' )->user();
            if ( $existedUser = User::whereEmail( $googleUser->getEmail() )->first() ) {
                $user = $existedUser;
            } else {
                $user = User::create( [
                    'name'     => $googleUser->getName(),
                    'email'    => $googleUser->getEmail(),
                    'password' => bcrypt( $googleUser->getId() )
                ] );
            }

            $this->guard()->loginUsingId( $user->id );

            return $this->authenticated( $request, Auth::user() );
        }
    }
