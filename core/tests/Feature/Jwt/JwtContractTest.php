<?php

    namespace Tests\Feature\Jwt;

    use App\Models\User;
    use Lcobucci\JWT\UnencryptedToken;
    use Nila\Jwt\Contracts\JwtContract;
    use Nila\Jwt\Exceptions\JwtException;
    use Nila\Jwt\Models\Session;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;
    use Tests\Factories\UserFactory;
    use Tests\TestCase;

    class JwtContractTest extends TestCase {

        private User $user;
        private Session $session;
        private string $token;

        /**
         * Setup the test environment.
         *
         * @return void
         */
        protected function setUp(): void {
            parent::setUp();
            $this->user    = UserFactory::createNormalUserWithSession();
            $this->session = $this->user->sessions()->latest()->first();
            $this->token   = UserFactory::createAccessToken( $this->user );
        }


        /**
         * @test
         *
         *
         * @return void
         */
        public function unauthenticatedUserException() {
            $this->getJson( route( 'test.me' ) )->assertUnauthorized()->assertJson( [
                'message' => 'Unauthenticated.'
            ] );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function setAuthenticatedUserSession() {
            $instance = $this->jwt->session( $this->session );
            $this->assertInstanceOf( JwtContract::class, $instance );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function setSessionFromRequest() {
            request()->headers->set( 'Authorization', 'Bearer ' . $this->token );

            $instance = $this->jwt->session();
            $this->assertInstanceOf( JwtContract::class, $instance );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function extractingToken() {
            $output = $this->jwt->session( $this->session )->extract( $this->token );
            $this->assertInstanceOf( UnencryptedToken::class, $output );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function settingUpJwtProviders() {
            $instance = $this->jwt->session( $this->session )->create( $this->user );
            $this->assertInstanceOf( JwtContract::class, $instance );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function settingAClaim() {
            $token = $this->jwt->session( $this->session )
                               ->create( $this->user )
                               ->claim( 'test', 'value' )
                               ->accessToken();

            $unToken = $this->jwt->session( $this->session )->getInsideToken( $token );

            $this->assertTrue( $unToken->claims()->has( 'test' ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function settingAHeader() {
            $token = $this->jwt->session( $this->session )
                               ->create( $this->user )
                               ->header( 'test', 'value' )
                               ->accessToken();

            $unToken = $this->jwt->session( $this->session )->getInsideToken( $token );

            $this->assertTrue( $unToken->headers()->has( 'test' ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function gettingAnAccessToken() {
            $token = $this->jwt->session( $this->session )
                               ->create( $this->user )
                               ->header( 'test', 'value' )
                               ->claim( 'test', 'value' )
                               ->accessToken();

            $this->assertIsString( $token );

            $unToken = $this->jwt->session( $this->session )->getInsideToken( $token );

            $this->assertTrue( $unToken->headers()->has( 'test' ) );
            $this->assertTrue( $unToken->claims()->has( 'test' ) );
        }


        /**
         * @test
         *
         *
         * @return void
         */
        public function validatingToken() {
            $this->assertTrue( $this->jwt->session( $this->session )->validate( $this->token ) );
            $this->assertTrue( $this->jwt->session( $this->session )->validateInsideToken( $this->token ) );

            $token = UserFactory::createAccessToken( UserFactory::createNormalUserWithSession() );

            $this->expectException( JwtException::class );
            $this->expectErrorMessage( 'Token signature mismatch!' );

            $this->jwt->session( $this->session )->assertInsideToken( $token );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function createARefreshToken() {
            $this->assertIsString( $refresh = $this->jwt->session( $this->session )
                                                        ->createRefreshToken( $this->user )
                                                        ->refreshToken() );
            $unRefresh = $this->jwt->session( $this->session )->extract( $refresh );

            $this->assertTrue( $unRefresh->headers()->has( 'refresh' ) );
            $this->assertTrue( (bool) $unRefresh->headers()->get( 'refresh' ) );

            $this->assertTrue( $unRefresh->headers()->has( 'session_id' ) );
            $this->assertEquals( $this->session->id, $unRefresh->headers()->get( 'session_id' ) );

            $insideRefresh = $this->jwt->session( $this->session )->getInsideToken( $refresh );
            $this->assertTrue( $insideRefresh->claims()->has( 'user_id' ) );
            $this->assertEquals( $this->user->id, $insideRefresh->claims()->get( 'user_id' ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function getUserPermissionsFromToken() {
            $this->assertInstanceOf( UnencryptedToken::class,
                $inside = $this->jwt->session( $this->session )->getInsideToken( $this->token ) );
            $this->assertTrue( $inside->claims()->has( 'permissions' ) );
            $this->assertEquals( $this->user->getAllPermissions()->pluck( 'name', 'id' )->toArray(),
                $this->jwt->session( $this->session )->getPermissions( $this->token ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function getInsideToken() {
            $this->assertInstanceOf( UnencryptedToken::class,
                $inside = $this->jwt->session( $this->session )->getInsideToken( $this->token ) );

            $this->assertTrue( $inside->claims()->has( 'role' ) );
            $this->assertEquals( $this->user->roles()->first()->only( 'id', 'name' ),
                $inside->claims()->get( 'role' ) );

            $this->assertTrue( $inside->claims()->has( 'permissions' ) );
            $this->assertEquals( $this->user->getAllPermissions()->pluck( 'name', 'id' )->toArray(),
                $inside->claims()->get( 'permissions' ) );

            $this->assertTrue( $inside->claims()->has( 'user' ) );
            $this->assertEquals( $this->user->only( 'id', 'name', 'email','version' ), $inside->claims()->get( 'user' ) );
        }
    }
