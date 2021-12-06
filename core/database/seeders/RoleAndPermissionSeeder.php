<?php

    namespace Database\Seeders;

    use App\Models\Post;
    use App\Models\User;
    use AreasEnum;
    use Illuminate\Database\Seeder;
    use Nila\Permissions\Exceptions\PermissionsException;
    use Nila\Permissions\Models\Role;
    use PermissionsSeeder;
    use RolesEnum;

    class RoleAndPermissionSeeder extends Seeder {
        /**
         * Run the database seeds.
         *
         * @return void
         * @throws PermissionsException
         * @throws \Throwable
         */
        public function run() {
            PermissionsSeeder::createPermissions( [
                // create basic permissions for given model plus additional permissions [ 'viewRole', 'viewAuthor' ]
                // you can Model::class => 'permission' if you have one additional permission
                User::class => [
                    'viewHorizon',

                    'viewPosts',
                    'updatePosts',
                    'attachPosts',
                    'detachPosts',
                ],
            ], AreasEnum::ADMIN // you can create permissions for a specific guard
            );
            PermissionsSeeder::createPermissions( [
                User::class => [ 'viewPosts' ],
                Post::class => [ 'viewOwner' ]
            ], AreasEnum::USER );

            PermissionsSeeder::createRoles( [
                // create roles
                RolesEnum::DEFAULT_ADMINS
            ], AreasEnum::ADMIN  // you can determine a guard for a set of roles. you can assign permissions
            // with same guard to a role
            );
            PermissionsSeeder::createRoles( [ RolesEnum::DEFAULT_USERS ], AreasEnum::USER );

            // assign permissions to the given roles
            PermissionsSeeder::assignPermissionsToRole( Role::findByName( RolesEnum::DEFAULT_ADMINS, AreasEnum::ADMIN ),
                [
                    // you can Model::class => '*' if you just want basic permissions to assign to the role
                    // also you can Model::class => 'permission' if you have one permission for a role
                    User::class => [
                        '*',

                        'viewHorizon',
                        'viewPosts',
                        'updatePosts',
                        'attachPosts',
                        'detachPosts',
                    ]
                ], AreasEnum::ADMIN // determine the permissions guard for assigning to a role
            // ( *notice : role must be in same guard as permissions are )
            );
            PermissionsSeeder::assignPermissionsToRole( Role::findByName( RolesEnum::DEFAULT_USERS, AreasEnum::USER ), [
                User::class => [ 'view' ],
                Post::class => [ 'view', 'viewOwner' ]
            ], AreasEnum::USER );

            // super permissions
            PermissionsSeeder::createSuperPermissions( [
                '*', // create *-* permission
                User::class => '*', // create user-*
                Post::class => '*',
            ], AreasEnum::ADMIN );
            // super permissions
            PermissionsSeeder::createSuperPermissions( [//Post::class => '*',
            ], AreasEnum::USER );

            // assign super permissions
            PermissionsSeeder::assignSuperPermissionsToRole( Role::findByName( RolesEnum::DEFAULT_USERS,
                AreasEnum::USER ), [//Post::class,

            ] );

            PermissionsSeeder::assignSuperPermissionsToRole( Role::findByName( RolesEnum::DEFAULT_ADMINS,
                AreasEnum::ADMIN ), [
                '*',
                User::class,
                Post::class,

            ] );

        }
    }
