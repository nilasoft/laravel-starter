<?php

    namespace Tests\Unit;

    use App\Models\User;
    use AreasEnum;
    use Nila\Permissions\Exceptions\PermissionsException;
    use Nila\Permissions\Models\Permission;
    use RolesEnum;
    use Tests\Factories\UserFactory;
    use Tests\TestCase;


    class UserModelTest extends TestCase {
        /**
         * @test
         *
         *
         * @return void
         */
        public function createACustomer() {
            $user = User::factory()->create();
            $this->assertInstanceOf( User::class, $user );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function createACustomerWithUsersRole() {
            $user = UserFactory::createAUser();
            $this->assertTrue( $user->hasRole( RolesEnum::DEFAULT_USERS ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function ACustomerWithUsersRoleCantGetARoleFromDifferentArea() {
            $user = UserFactory::createAUser();

            $this->expectException( PermissionsException::class );
            $this->expectExceptionMessage( "The " . RolesEnum::DEFAULT_ADMINS . " role not found in the " . AreasEnum::USER . " area!" );

            $user->assignRole( RolesEnum::DEFAULT_ADMINS );

            $this->assertFalse( $user->hasRole( RolesEnum::DEFAULT_ADMINS ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function ACustomerWithUsersRoleCantGetAPermissionFromDifferentArea() {
            $user       = UserFactory::createAUser();
            $permission = Permission::whereNotIn( 'name',
                Permission::where( 'area', AreasEnum::USER )->pluck( 'name' ) )->first()->name;

            $this->expectException( PermissionsException::class );
            $this->expectExceptionMessage( "The $permission permission not found in the " . AreasEnum::USER . " area!" );
            $user->givePermissionTo( $permission );

            $this->assertFalse( $user->hasPermissionTo( $permission ) );
        }
    }
