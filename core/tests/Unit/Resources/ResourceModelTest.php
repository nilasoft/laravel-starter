<?php


    namespace Tests\Unit\Resources;


    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Facades\URL;
    use Nila\Resources\Models\Resource as ResourceModel;
    use Tests\TestCase;

    class ResourceModelTest extends TestCase {

        /**
         * @test
         *
         *
         * @return void
         */
        public function generateUrl() {
            $response = $this->postJson( route( 'resources.test.upload', [ 'field' => 'file' ] ), [
                'file' => UploadedFile::fake()->image( 'g-eazy.png', 1080, 1080 )
            ] );
            $response->assertCreated();
            $data = json_decode( $response->content() );
            $this->assertDatabaseHas( ResourceModel::class, [ 'id' => $data->id ] );
            $model = ResourceModel::findOrFail( $data->id );
            $this->assertEquals( substr( $link = URL::temporarySignedRoute( 'resources.download',
                now()->addMinutes( $this->resourcesConfig[ 'expiration' ] ), [
                    'resource' => $model->id,
                    'hash'     => $this->signature->create()
                ] ), 0, strpos( $link, '?' ) ), substr( $link = $model->url, 0, strpos( $link, '?' ) ) );

            $this->assertTrue( $this->resources->delete( $data->id ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function generateHlsUrl() {
            $this->withoutExceptionHandling();
            $response = $this->postJson( route( 'resources.test.upload', [ 'field' => 'generateHlsUrl' ] ), [
                'generateHlsUrl' => UploadedFile::fake()
                                                ->createWithContent( 'video.mp4',
                                                    file_get_contents( __DIR__ . '/../../resources/video.mp4' ) )
            ] );
            $response->assertCreated();
            $data = json_decode( $response->content() );
            $this->assertDatabaseHas( ResourceModel::class, [ 'id' => $data->id ] );
            $model = ResourceModel::findOrFail( $data->id );
            $this->assertEquals( url( 'resources/' . $model->path . '/' . $model->hls ), $model->hlsUrl );

            $this->assertTrue( $this->resources->delete( $model->id ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function getDirectLinkIfHlsIsDisabled() {
            // $this->withoutExceptionHandling();
            $this->app[ 'config' ]->set( 'resources.hls.enable', false );
            $response = $this->postJson( route( 'resources.test.upload', [ 'field' => 'generateHlsUrl' ] ), [
                'generateHlsUrl' => UploadedFile::fake()
                                                ->createWithContent( 'video.mp4',
                                                    file_get_contents( __DIR__ . '/../../resources/video.mp4' ) )
            ] );
            $response->assertCreated();
            $data = json_decode( $response->content() );
            $this->assertDatabaseHas( ResourceModel::class, [ 'id' => $data->id ] );
            $model = ResourceModel::findOrFail( $data->id );
            $this->assertEquals( url( 'resources/' . $model->path . '/' . $model->file ), $model->hlsUrl );

            $this->assertTrue( $this->resources->delete( $model->id ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function getExternalLink() {
            $response = $this->postJson( route( 'resources.test.external', [ 'field' => 'external' ] ), [
                'external' => $link = 'http://laravel.com/img/homepage/vapor.jpg'
            ] );
            $response->assertCreated();
            $data = json_decode( $response->content() );
            $this->assertDatabaseHas( ResourceModel::class, [ 'id' => $data->id ] );
            $model = ResourceModel::findOrFail( $data->id );
            $this->assertTrue( $model->isExternal() );
            $this->assertEquals( $link, $model->url );

            $this->assertTrue( $this->resources->delete( $model->id ) );
        }
    }
