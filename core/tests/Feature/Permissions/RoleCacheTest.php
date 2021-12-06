<?php

    namespace Tests\Feature\Permissions;

    use AreasEnum;
    use Illuminate\Support\Facades\Cache;
    use Nila\Permissions\Models\Role;
    use PermissionsCacheEnum;
    use Tests\TestCase;

    class RoleCacheTest extends TestCase {
        /**
         * @test
         *
         *
         * @return void
         */
        public function onCreating() {
            Cache::spy();

            $role = Role::create( [ 'name' => 'new role', 'area' => AreasEnum::ADMIN ] );

            Cache::shouldHaveReceived( 'forever' )->with( PermissionsCacheEnum::ROLE . $role->id,$role );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function onDeleting() {
            $role = Role::create( [ 'name' => 'new role', 'area' => AreasEnum::ADMIN ] );
            Cache::spy();

            $role->delete();

            Cache::shouldHaveReceived( 'forget' )->with( PermissionsCacheEnum::ROLE . $role->id );
        }

        /**
         * @test
         *
         *
         * @return void
         * @throws \Nila\Permissions\Exceptions\PermissionsException
         */
        public function onUpdate() {
            $role = Role::findById( 1 );

            Cache::spy();

            $role->update( [ 'name' => 'new role name' ] );

            Cache::shouldHaveReceived( 'forget' )->with( PermissionsCacheEnum::ROLE . $role->id );
            Cache::shouldHaveReceived( 'forever' )->with( PermissionsCacheEnum::ROLE . $role->id, $role );
        }
    }
