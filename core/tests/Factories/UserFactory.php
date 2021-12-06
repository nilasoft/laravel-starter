<?php


    namespace Tests\Factories;


    use App\Models\User;
    use AreasEnum;
    use Nila\Jwt\Contracts\JwtContract;
    use Nila\Permissions\Models\Role;
    use RolesEnum;

    class UserFactory {
        public static function createAUser(): User {
            $user = User::factory()->create();
            $user->assignRole( Role::findByName( RolesEnum::DEFAULT_USERS, AreasEnum::USER ) );

            return $user->fresh();
        }

        public static function createAUserWithoutRole(): User {
            $user = User::factory()->create();

            return $user->fresh();
        }

        public static function createNormalUserWithSession(): User {
            $user = User::factory()->create();
            $user->assignRole( Role::findByName( RolesEnum::DEFAULT_USERS, AreasEnum::USER ) );
            $user->sessions()->create( [
                'ip'       => '127.0.0.' . rand( 0, 255 ),
                'device'   => 'Nokia 5.3',
                'platform' => 'Android 11',
                'secret'   => \Illuminate\Support\Str::random()
            ] );

            return $user->fresh();
        }

        public static function createAccessToken( User $user ): string {
            return app( JwtContract::class )
                ->session( $user->sessions()->latest()->first() )
                ->create( $user )
                ->accessToken();
        }

        public static function createRefreshToken( User $user ): string {
            if ( ! $session = $user->sessions()->latest()->first() ) {
                throw new \Exception( 'User didnt have session!' );
            }

            return app( JwtContract::class )->session( $session )->createRefreshToken( $user )->refreshToken();
        }
    }
