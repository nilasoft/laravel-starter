<?php

    namespace Tests\Feature\Permissions;

    use App\Models\User;
    use AreasEnum;
    use Nila\Permissions\Exceptions\PermissionsException;
    use Nila\Permissions\Models\Permission;
    use Tests\Factories\UserFactory;
    use Tests\TestCase;

    class HasPermissionsTraitTest extends TestCase {

        private Permission $normalPermission, $adminPermission;
        private User $user, $normalUser, $normalUserWithRole;

        /**
         * Setup the test environment.
         *
         * @return void
         */
        protected function setUp(): void {
            parent::setUp();
            $this->normalPermission   = Permission::firstWhere( 'area', AreasEnum::USER );
            $this->adminPermission    = Permission::firstWhere( 'area', AreasEnum::ADMIN );
            $this->user               = UserFactory::createAUserWithoutRole();
            $this->normalUser         = UserFactory::createAUserWithoutRole();
            $this->normalUserWithRole = UserFactory::createAUser();
            $this->normalUser->syncPermissions( $this->normalPermission );
        }


        /**
         * @test
         *
         *
         * @return void
         */
        public function hasPermissionTo() {
            $this->assertFalse( $this->user->hasPermissionTo( $this->normalPermission ) );
            $this->assertTrue( $this->normalUser->hasPermissionTo( $this->normalPermission ) );
            $this->assertFalse( $this->normalUser->hasPermissionTo( $this->adminPermission ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function getDirectPermissions() {
            $permissions = Permission::where( 'area', AreasEnum::USER )->take( 3 )->get();
            $this->user->syncPermissions( ... $permissions->pluck( 'id' )->toArray() );

            $this->assertEquals( $this->user->permissions, $this->user->getDirectPermissions() );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function hasAnyPermission() {
            $this->assertFalse( $this->user->hasAnyPermission( $this->normalPermission ) );

            $this->assertTrue( $this->normalUser->hasAnyPermission( 999, $this->normalPermission ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function hasAllPermissions() {
            $this->assertFalse( $this->user->hasAllPermissions( $this->normalPermission ) );
            $this->assertFalse( $this->normalUser->hasAllPermissions( 999, $this->normalPermission ) );

            $this->assertTrue( $this->normalUser->hasAllPermissions( $this->normalPermission ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function hasDirectPermission() {
            $this->assertFalse( $this->user->hasDirectPermission( $this->normalPermission ) );
            $this->assertFalse( $this->normalUser->hasDirectPermission( 999, $this->normalPermission ) );

            $this->assertTrue( $this->normalUser->hasDirectPermission( $this->normalPermission ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function hasAnyDirectPermission() {
            $this->assertFalse( $this->user->hasAnyDirectPermission( $this->normalPermission ) );

            $this->assertTrue( $this->normalUser->hasAnyDirectPermission( 999, $this->normalPermission ) );
            $this->assertTrue( $this->normalUser->hasAnyDirectPermission( $this->normalPermission ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function hasAllDirectPermissions() {
            $this->assertFalse( $this->user->hasAllDirectPermissions( $this->normalPermission ) );
            $this->assertFalse( $this->normalUser->hasAllDirectPermissions( 999, $this->normalPermission ) );

            $this->assertTrue( $this->normalUser->hasAllDirectPermissions( $this->normalPermission ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function getPermissionsViaRoles() {
            $this->assertEquals( collect(), $this->user->getPermissionsViaRoles() );

            $this->assertEquals( $this->normalUserWithRole->roles()->first()->permissions,
                $this->normalUserWithRole->getPermissionsViaRoles() );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function getAllPermissions() {
            $this->assertEquals( collect( $this->normalUserWithRole->getPermissionsViaRoles() )
                ->merge( $this->normalUserWithRole->getDirectPermissions() )
                ->toArray(), $this->normalUserWithRole->getAllPermissions()->toArray() );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function givePermissionTo() {

            $this->assertFalse( $this->user->hasPermissionTo( $this->normalPermission ) );

            $this->user->givePermissionTo( $this->normalPermission );

            $this->assertTrue( $this->user->hasPermissionTo( $this->normalPermission ) );
            $this->assertFalse( $this->user->hasPermissionTo( $this->adminPermission ) );

            // assign a permissions from different area
            $this->expectException( PermissionsException::class );
            $this->expectErrorMessage( "The " . $this->adminPermission->id . " permission not found in the " . $this->normalPermission->area . " area!" );
            $this->user->givePermissionTo( $this->adminPermission );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function syncPermissions() {
            $permissions = Permission::where( 'area', AreasEnum::USER )->take( 3 )->get();
            $this->user->syncPermissions( ... $permissions->pluck( 'id' )->toArray() );

            $this->assertTrue( $this->user->hasAllPermissions( ... $permissions ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function revokePermissionTo() {
            $permissions = Permission::where( 'area', AreasEnum::USER )->take( 3 )->get();
            $this->user->syncPermissions( ... $permissions->pluck( 'id' )->toArray() );

            $this->assertEquals( $this->user->permissions, $this->user->getDirectPermissions() );

            $this->user->revokePermissionTo( ... $permissions );

            $this->assertFalse( $this->user->hasAllDirectPermissions( ...$permissions ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function getPermissionNames() {
            $this->assertEquals( $this->normalUserWithRole->permissions->pluck( 'name' ),
                $this->normalUserWithRole->getPermissionNames() );
        }


    }
