<?php

    namespace Tests\Feature\Jwt;

    use App\Models\User;
    use Illuminate\Support\Facades\Cache;
    use JwtCacheEnum;
    use Tests\TestCase;

    class SessionCacheTest extends TestCase {
        /**
         * @test
         *
         *
         * @return void
         */
        public function onCreating() {
            Cache::spy();

            $session = User::findOrFail( 1 )->sessions()->create( [
                'ip'       => 'fake data',
                'device'   => 'fake data',
                'platform' => 'fake data',
                'secret'   => 'fake data'
            ] );

            Cache::shouldHaveReceived( 'forever' )
                 ->with( JwtCacheEnum::SESSION . $session->id, $session->getForCache() );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function onUpdate() {
            $session = User::findOrFail( 1 )->sessions()->create( [
                'ip'       => 'fake data',
                'device'   => 'fake data',
                'platform' => 'fake data',
                'secret'   => 'fake data'
            ] );
            Cache::spy();

            $session->update( [ 'ip' => '127.0.0.0' ] );

            Cache::shouldHaveReceived( 'forget' )->with( JwtCacheEnum::SESSION . $session->id );
            Cache::shouldHaveReceived( 'forever' )
                 ->with( JwtCacheEnum::SESSION . $session->id, $session->getForCache() );
        }


        /**
         * @test
         *
         *
         * @return void
         */
        public function onDeleting() {
            $session = User::findOrFail( 1 )->sessions()->create( [
                'ip'       => 'fake data',
                'device'   => 'fake data',
                'platform' => 'fake data',
                'secret'   => 'fake data'
            ] );

            Cache::spy();

            $session->delete();

            Cache::shouldHaveReceived( 'forget' )->with( JwtCacheEnum::SESSION . $session->id );
        }
    }
