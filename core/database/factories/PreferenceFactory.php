<?php

    namespace Database\Factories;

    use App\Models\Preference;
    use Illuminate\Database\Eloquent\Factories\Factory;

    class PreferenceFactory extends Factory {
        /**
         * The name of the factory's corresponding model.
         *
         * @var string
         */
        protected $model = Preference::class;

        /**
         * Define the model's default state.
         *
         * @return array
         */
        public function definition() {
            return [
                'key'   => $this->faker->firstNameFemale(),
                'value' => $this->faker->colorName()
            ];
        }

        public function fill( string $key, $value ) {
            return $this->state( [
                'key'   => $key,
                'value' => $value
            ] );
        }
    }
