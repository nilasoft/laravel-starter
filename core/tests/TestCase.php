<?php

    namespace Tests;

    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
    use Illuminate\Foundation\Testing\WithFaker;
    use Illuminate\Support\Facades\App;
    use Illuminate\Support\Facades\Storage;
    use Nila\Jwt\Contracts\JwtContract;
    use Nila\Menus\Contracts\MenusContract;
    use Nila\Payments\Contracts\PaymentsContract;
    use Nila\Permissions\Contracts\PermissionsContract;
    use Nila\Resources\Contracts\ResourcesContract;
    use Nila\Resources\Contracts\SignatureContract;

    abstract class TestCase extends BaseTestCase {
        use CreatesApplication, RefreshDatabase, WithFaker;


        protected ResourcesContract $resources;
        protected Filesystem $storage;
        protected SignatureContract $signature;
        protected array $resourcesConfig;

        protected MenusContract $menus;
        protected array $menusConfig;

        protected JwtContract $jwt;
        protected array $jwtConfig;

        protected PermissionsContract $permissions;
        protected array $permissionsConfig;

        protected PaymentsContract $payments;
        protected array $paymentsConfig;

        /**
         * Setup the test environment.
         *
         * @return void
         */
        protected function setUp(): void {
            parent::setUp();
            $this->resources         = App::make( ResourcesContract::class );
            $this->storage           = Storage::disk( 'resources' );
            $this->signature         = App::make( SignatureContract::class );
            $this->resourcesConfig   = config( 'resources' );
            $this->menus             = app( MenusContract::class );
            $this->menusConfig       = config( 'menus' );
            $this->jwt               = app( JwtContract::class );
            $this->jwtConfig         = config( 'jwt' );
            $this->permissions       = app( PermissionsContract::class );
            $this->permissionsConfig = config( 'permissions' );
            $this->payments          = app( PaymentsContract::class );
            $this->paymentsConfig    = config( 'payments' );
        }


    }
