<?php


    namespace App\Policies;


    use Illuminate\Support\Arr;

    trait PolicyShortHandTrait {
        public function getModel(): string {
            return strtolower( substr( $string = Arr::last( explode( '\\', self::class ) ), 0,
                strpos( $string, 'Policy' ) ) );
        }
    }
