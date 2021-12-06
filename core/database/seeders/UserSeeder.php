<?php

    namespace Database\Seeders;

    use App\Models\User;
    use Illuminate\Database\Seeder;
    use RolesEnum;

    class UserSeeder extends Seeder {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run() {
            $user = User::factory()->create();
            $user->assignRole( RolesEnum::DEFAULT_ADMINS, true );

            foreach ( range( 1, 5 ) as $item ) {
                $user = User::factory()->create();
                $user->assignRole( RolesEnum::DEFAULT_USERS, true );
            }
        }
    }
