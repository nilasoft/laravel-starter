<?php


    namespace Tests\Feature\Resources;

    use Nila\Resources\Models\Resource as ResourceModel;
    use Tests\TestCase;

    class ExternalLinkStoreTest extends TestCase {

        /**
         * @test
         *
         *
         * @return void
         */
        public function externalLinkStore() {
            $response = $this->postJson( route( 'resources.test.external', [ 'field' => 'link' ] ), [
                'link' => $link = 'http://laravel.com/img/homepage/vapor.jpg'
            ] );

            $response->assertCreated()->assertJsonStructure( [
                'id',
                'path',
                'file',
                'extension',
                'options'
            ] );
            $this->assertDatabaseHas( ResourceModel::class, [
                'title'        => 'vapor',
                'path'         => $link,
                'extension'    => 'jpg',
                'external'     => "1",
                'published_at' => now()
            ] );
        }
    }
