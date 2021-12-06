<?php

    namespace Tests\Feature\Permissions;

    use Illuminate\Database\Eloquent\Collection;
    use Nila\Permissions\Models\Permission;
    use Nila\Permissions\Models\Role;
    use Tests\TestCase;

    class PermissionsContractTest extends TestCase {

        /**
         * @test
         *
         *
         * @return void
         */
        public function findRoleMethod() {
            $role = Role::firstOrFail();

            $this->assertInstanceOf( Role::class, $foundRole = $this->permissions->findRole( $role ) );
            $this->assertEquals( $role->id, $foundRole->id );

            $this->assertInstanceOf( Role::class, $foundRole = $this->permissions->findRole( $role->id ) );
            $this->assertEquals( $role->id, $foundRole->id );

            $this->assertInstanceOf( Role::class, $foundRole = $this->permissions->findRole( $role->name ) );
            $this->assertEquals( $role->id, $foundRole->id );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function findAllRoles() {
            $this->assertInstanceOf( Collection::class, $findAll = $this->permissions->findAllRoles() );

            $this->assertEquals( Role::all(), $findAll );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function findAnyRoles() {
            $roles = Role::take( 2 )->get();

            $this->assertEquals( collect( $roles ),
                $this->permissions->findAnyRoles( $roles->first(), $roles->last()->id, 999 ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function findPermission() {
            $permission = Permission::take( 1 )->first();

            $this->assertInstanceOf( Permission::class, $this->permissions->findPermission( $permission ) );
            $this->assertInstanceOf( Permission::class, $this->permissions->findPermission( $permission->id ) );
            $this->assertInstanceOf( Permission::class, $this->permissions->findPermission( $permission->name ) );

            $this->assertEquals( $permission, $this->permissions->findPermission( $permission ) );
        }


        /**
         * @test
         *
         *
         * @return void
         */
        public function findAllPermissions() {
            $this->assertInstanceOf( Collection::class, $findAll = $this->permissions->findAllPermissions() );

            $this->assertEquals( Permission::all(), $findAll );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function findAnyPermissions() {
            $permissions = Permission::take( 2 )->get();

            $this->assertEquals( collect( $permissions ),
                $this->permissions->findAnyPermissions( $permissions->first(), $permissions->last()->id, 999 ) );
        }

    }
