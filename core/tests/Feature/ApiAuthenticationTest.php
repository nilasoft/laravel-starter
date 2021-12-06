<?php

    namespace Tests\Feature;

    use App\Models\User;
    use Tests\Factories\UserFactory;
    use Tests\TestCase;

    class ApiAuthenticationTest extends TestCase {

        /**
         * @test
         *
         *
         * @return void
         */
        public function register() {
            $response = $this->postJson( route( 'api.register' ), [
                'name'                  => $name = 'hans',
                'email'                 => $email = 'hans@email.com',
                'password'              => 'password',
                'password_confirmation' => 'password'
            ] )->assertCreated()->assertJsonStructure( [
                'access_token',
                'refresh_token',
                'user'
            ] );
            $this->assertDatabaseHas( User::class, [ 'email' => $email ] );
            $data  = json_decode( $response->getContent() );
            $token = $data->access_token;
            request()->headers->set( 'Authorization', 'Bearer ' . $token );

            $unToken = $this->jwt->session()->getInsideToken( $token );

            $this->assertTrue( $unToken->claims()->has( 'user' ) );
            $this->assertEquals( [
                'id'      => ( $user = User::whereEmail( $email )->first() )->id,
                'name'    => $name,
                'email'   => $email,
                'version' => $user->getVersion()
            ], $unToken->claims()->get( 'user' ) );

        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function login() {
            $user     = UserFactory::createAUser();
            $response = $this->postJson( route( 'api.login' ), [
                'email'    => $user->email,
                'password' => 'password', // default password
            ] )->assertOk()->assertJsonStructure( [
                'access_token',
                'refresh_token',
                'user'
            ] );

            $data  = json_decode( $response->getContent() );
            $token = $data->access_token;
            request()->headers->set( 'Authorization', 'Bearer ' . $token );

            $unToken = $this->jwt->session()->getInsideToken( $token );

            $this->assertTrue( $unToken->claims()->has( 'user' ) );
            $this->assertEquals( [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'version' => $user->getVersion()
            ], $unToken->claims()->get( 'user' ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function FailedIfRequestForRegistrationIsNotValid() {
            $this->postJson( route( 'api.register' ), [
                'name'                  => [ 1 ],
                'email'                 => $email = 'hans@@email.c4',
                'password'              => 'password',
                'password_confirmation' => 'passwordw',
            ] )->assertJsonValidationErrors( [
                'name',
                'email',
                'password',
            ] );

            $this->assertDatabaseMissing( User::class, [ 'email' => $email ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function FailedIfRequestForLoginIsNotValid() {
            $this->postJson( route( 'api.login' ), [
                'email'    => 'itsNotTheUserEmail.fail.ed',
                'password' => 'password5',
            ] )->assertJsonValidationErrors( [
                'email'
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function FailedIfCredentialsForLoginIsNotValid() {
            $this->postJson( route( 'api.login' ), [
                'email'    => 'itsNotTheUserEmail@fail.ed',
                'password' => 'password5',
                'ip'       => '192.168.1.1',
                'device'   => 'Laptop'
            ] )->assertJsonValidationErrors( [
                'email'
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function refreshToken() {
            $user         = UserFactory::createNormalUserWithSession();
            $refreshToken = UserFactory::createRefreshToken( $user );

            $this->postJson( route( 'api.refresh.token' ), headers: [
                'Authorization' => 'Bearer ' . $refreshToken
            ] )->assertOk()->assertJsonStructure( [
                'access_token',
                'refresh_token',
                'user'
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function refreshTokenExpired() {
            config()->set( 'jwt.refreshExpired_at', '-4 second' );
            $user  = UserFactory::createNormalUserWithSession();
            $token = $this->jwt->setConfig( config( 'jwt' ) )
                               ->session( $user->sessions()->first() )
                               ->createRefreshToken( $user )
                               ->refreshToken();

            $this->postJson( route( 'api.refresh.token' ), headers: [
                'Authorization' => 'Bearer ' . $token
            ] )->assertUnprocessable()->assertJsonValidationErrors( [
                'refresh_token' => 'Refresh token expired!'
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function refreshTokenTokenRequired() {
            $this->postJson( route( 'api.refresh.token' ) )
                 ->assertUnprocessable()
                 ->assertJsonValidationErrors( [ 'token' ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function refreshTokenTokenSend() {
            $user  = UserFactory::createNormalUserWithSession();
            $token = UserFactory::createAccessToken( $user );

            $this->postJson( route( 'api.refresh.token' ), headers: [
                'Authorization' => 'Bearer ' . $token
            ] )->assertUnprocessable()->assertJsonValidationErrors( [
                'token' => "Please send a refresh token!"
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function forgotPassword() {
            $user = UserFactory::createNormalUserWithSession();
            $this->postJson( route( 'api.forgot.password' ), [
                'email' => $user->email
            ] )->assertOk()->assertJson( [
                'message' => 'We have emailed your password reset link!'
            ] );
        }

    }
