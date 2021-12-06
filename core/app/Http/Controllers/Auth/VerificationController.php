<?php

    namespace App\Http\Controllers\Auth;

    use App\Http\Controllers\Controller;
    use App\Models\User;
    use App\Providers\RouteServiceProvider;
    use Illuminate\Auth\Access\AuthorizationException;
    use Illuminate\Auth\Events\Registered;
    use Illuminate\Auth\Events\Verified;
    use Illuminate\Foundation\Auth\VerifiesEmails;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Auth;

    class VerificationController extends Controller {
        /*
        |--------------------------------------------------------------------------
        | Email Verification Controller
        |--------------------------------------------------------------------------
        |
        | This controller is responsible for handling email verification for any
        | user that recently registered with the application. Emails may also
        | be re-sent if the user didn't receive the original email message.
        |
        */

        use VerifiesEmails;

        /**
         * Where to redirect users after verification.
         *
         * @var string
         */
        protected $redirectTo = RouteServiceProvider::HOME;

        /**
         * Mark the authenticated user's email address as verified.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
         *
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        public function verify( Request $request ) {
            if ( ! hash_equals( (string) $request->route( 'id' ), (string) $request->user()->getKey() ) ) {
                throw new AuthorizationException;
            }

            if ( ! hash_equals( (string) $request->route( 'hash' ),
                sha1( $request->user()->getEmailForVerification() ) ) ) {
                throw new AuthorizationException;
            }

            if ( $request->user()->hasVerifiedEmail() ) {
                return $request->wantsJson() ? new JsonResponse( [], 204 ) : redirect( $this->redirectPath() );
            }

            if ( $request->user()->markEmailAsVerified() ) {
                event( new Verified( $request->user() ) );
            }

            if ( $response = $this->verified( $request ) ) {
                return $response;
            }

            return $request->wantsJson() ? new JsonResponse( [],
                204 ) : redirect( $this->redirectPath() )->with( 'verified', true );
        }

        public function redirectTo() {
            // should redirect to the SPA app and notify the result
            return '/';
        }

        /**
         * The user has been verified.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return mixed
         */
        protected function verified( Request $request ) {
            return $request->wantsJson() ? new JsonResponse( [
                'message' => 'Verification successful!',
            ], 200 ) : redirect( $this->redirectPath() )->with( 'verified', true );
        }


        /**
         * Resend the email verification notification.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
         */
        public function resend( Request $request ) {
            if ( $request->user()->hasVerifiedEmail() ) {
                return $request->wantsJson() ? new JsonResponse( [
                    'message' => 'Email already verified!'
                ], 200 ) : redirect( $this->redirectPath() );
            }

            $request->user()->sendEmailVerificationNotification();

            return $request->wantsJson() ? new JsonResponse( [
                'message' => 'Email sent to your Inbox.'
            ], 200 ) : back()->with( 'resent', true );
        }
    }
