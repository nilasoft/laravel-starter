<?php

    namespace Nila\Permissions;

    use Nila\Permissions\Models\Permission;
    use Nila\Permissions\Models\Role;
    use Nila\Permissions\Policies\PermissionPolicy;
    use Nila\Permissions\Policies\RolePolicy;
    use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

    class PermissionsAuthServiceProvider extends AuthServiceProvider {
        /**
         * The policy mappings for the application.
         *
         * @var array
         */
        protected $policies = [
            Role::class       => RolePolicy::class,
            Permission::class => PermissionPolicy::class,

        ];

        /**
         * Register any authentication / authorization services.
         *
         * @return void
         */
        public function boot() {
            $this->registerPolicies();

        }
    }
