<?php

    namespace Database\Seeders;

    use App\Models\Preference;
    use Illuminate\Database\Seeder;

    class PreferenceSeeder extends Seeder {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run() {
            set_batch_preferences( [
                'name' => 'Starter',
            ] );
        }
    }
