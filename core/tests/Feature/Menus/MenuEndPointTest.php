<?php

    namespace Tests\Feature\Menus;

    use Nila\Jwt\Contracts\JwtContract;
    use Nila\Menus\Http\Resources\MenusCollection;
    use Tests\Factories\UserFactory;
    use Tests\TestCase;

    class MenuEndPointTest extends TestCase {
        /**
         * @test
         *
         *
         * @return void
         */
        public function unauthenticatedUserAccessDenying() {
            $response = $this->postJson( route( 'menus.api' ) );

            $response->assertUnauthorized();
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function userCanGetPermittedMenus() {
            $user  = UserFactory::createNormalUserWithSession();
            $token = UserFactory::createAccessToken( $user );

            $this->postJson( route( 'menus.api' ), headers: [
                'Authorization' => 'Bearer ' . $token
            ] )->assertOk()->assertExactJson( MenusCollection::make( $this->menus->findAll() )
                                                             ->response()
                                                             ->getData( true ) );
        }
    }
