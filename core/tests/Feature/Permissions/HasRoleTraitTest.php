<?php

    namespace Tests\Feature\Permissions;

    use App\Models\User;
    use AreasEnum;
    use Nila\Permissions\Exceptions\PermissionsException;
    use Nila\Permissions\Models\Permission;
    use Nila\Permissions\Models\Role;
    use Tests\Factories\UserFactory;
    use Tests\TestCase;

    class HasRoleTraitTest extends TestCase {

        protected User $user, $normalUser;
        protected Role $normalRole, $adminRole;

        /**
         * Setup the test environment.
         *
         * @return void
         */
        protected function setUp(): void {
            parent::setUp();
            $this->user       = UserFactory::createAUserWithoutRole();
            $this->normalUser = UserFactory::createAUserWithoutRole();
            $this->normalRole = Role::firstWhere( 'area', AreasEnum::USER );
            $this->normalUser->assignRole( $this->normalRole );
            $this->adminRole = Role::firstWhere( 'area', AreasEnum::ADMIN );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function assignRoleAndHasRole() {
            $this->assertFalse( $this->user->hasRole( $this->normalRole ) );
            $this->user->assignRole( $this->normalRole->name );
            $this->assertTrue( $this->user->hasRole( $this->normalRole ) );


            $this->expectException( PermissionsException::class );
            $this->expectExceptionMessage( "The " . $this->adminRole->id . " role not found in the " . $this->normalRole->area . " area!" );
            $this->user->assignRole( $this->adminRole );
            $this->assertFalse( $this->user->hasRole( $this->adminRole ) );
        }


        /**
         * @test
         *
         *
         * @return void
         */
        public function hasAnyRole() {

            $this->assertTrue( $this->normalUser->hasAnyRole( 999, $this->normalRole->name ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function getRoleName() {
            $this->assertEquals( $this->normalRole->name, $this->normalUser->getRoleName() );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function getRole() {
            $this->assertNull( $this->user->getRole() );
            $this->assertInstanceOf( Role::class, $this->normalUser->getRole() );
        }
    }
