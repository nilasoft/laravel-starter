<?php

    namespace Tests\Unit\Permissions;

    use AreasEnum;
    use Nila\Permissions\Exceptions\PermissionsException;
    use Nila\Permissions\Models\Permission;
    use Tests\TestCase;

    class PermissionModelTest extends TestCase {
        private Permission $normalPermission;

        /**
         * Setup the test environment.
         *
         * @return void
         */
        protected function setUp(): void {
            parent::setUp();
            $this->normalPermission = Permission::where( 'area', AreasEnum::USER )
                                                ->whereNotIn( 'name', Permission::where( 'area', AreasEnum::ADMIN )
                                                                                ->pluck( 'name' )
                                                                                ->toArray() )
                                                ->first();
        }

        /**
         * @test
         *
         *
         * @return void
         * @throws PermissionsException
         */
        public function findByName() {
            $role = Permission::findByName( $this->normalPermission->name, AreasEnum::USER );
            $this->assertInstanceOf( Permission::class, $role );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function findByNameInWrongArea() {
            $this->expectException( PermissionsException::class );
            $this->expectExceptionMessage( 'The ' . $this->normalPermission->name . ' permission not found in the ' . AreasEnum::ADMIN . ' area!' );
            Permission::findByName( $this->normalPermission->name, AreasEnum::ADMIN );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function findByNameNonExistedPermission() {
            $name = 'non-exists';
            $this->expectException( PermissionsException::class );
            $this->expectExceptionMessage( 'The ' . $name . ' permission not found in the all areas!' );
            Permission::findByName( $name );
        }


        /**
         * @test
         *
         *
         * @return void
         * @throws PermissionsException
         */
        public function findById() {
            $role = Permission::findById( $this->normalPermission->id, AreasEnum::USER );
            $this->assertInstanceOf( Permission::class, $role );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function findByIdInWrongArea() {
            $this->expectException( PermissionsException::class );
            $this->expectExceptionMessage( 'The ' . $this->normalPermission->id . ' permission not found in the ' . AreasEnum::ADMIN . ' area!' );
            Permission::findById( $this->normalPermission->id, AreasEnum::ADMIN );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function findByIdNonExistedPermission() {
            $id = 267;
            $this->expectException( PermissionsException::class );
            $this->expectExceptionMessage( 'The ' . $id . ' permission not found in the all areas!' );
            Permission::findById( $id );
        }


        /**
         * @test
         *
         *
         * @return void
         * @throws PermissionsException
         */
        public function findOrCreate() {
            $role = [
                'name' => 'new role',
                'area' => AreasEnum::USER
            ];

            $createdPermission = Permission::findOrCreate( ...$role );
            $this->assertTrue( $createdPermission->wasRecentlyCreated );

            Permission::findById( $createdPermission->id, $createdPermission->area );
        }


    }
