<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use Nila\Payments\Contracts\PaymentsContract;
    use Nila\Payments\Models\Dtos\PurchaseDto;
    use Nila\Payments\Models\Requests\DepositManualRequest;
    use Nila\Payments\Models\Requests\WithdrawManualRequest;
    use Nila\Payments\Models\Wallet;

    class DatabaseSeeder extends Seeder {
        /**
         * Seed the application's database.
         *
         * @return void
         * @throws \Throwable
         */
        public function run() {
            $this->call( [
                RoleAndPermissionSeeder::class,
                UserSeeder::class,
                PreferenceSeeder::class,
                PostSeeder::class,

            ] );
        }
    }
