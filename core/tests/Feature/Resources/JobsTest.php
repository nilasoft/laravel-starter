<?php


    namespace Tests\Feature\Resources;


    use Illuminate\Http\UploadedFile;
    use Nila\Resources\Models\Resource as ResourceModel;
    use Tests\TestCase;

    class JobsTest extends TestCase {

        /**
         * @test
         *
         *
         * @return void
         */
        public function ImageJobs() {
            $response = $this->postJson( route( 'resources.test.upload', [ 'field' => 'ImageJobs' ] ), [
                'ImageJobs' => UploadedFile::fake()
                                           ->createWithContent( 'posty.jpg',
                                               file_get_contents( __DIR__ . '/../../resources/posty.jpg' ) )
            ] );
            $response->assertJsonStructure( [
                'id',
                'path',
                'file',
                'extension',
                'options'
            ] );
            $data = json_decode( $response->content() );

            $this->assertDatabaseHas( ResourceModel::class, [
                'id' => $data->id
            ] );
            $this->assertGreaterThanOrEqual( $data->options->size, filesize( __DIR__ . '/../../resources/posty.jpg' ) );

            $this->assertTrue( $this->resources->delete( $data->id ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function VideoJobs() {
            $this->withoutExceptionHandling();
            $response = $this->postJson( route( 'resources.test.upload', [ 'field' => 'VideoJobs' ] ), [
                'VideoJobs' => \Illuminate\Http\UploadedFile::fake()
                                                            ->createWithContent( 'video.mp4',
                                                                file_get_contents( __DIR__ . '/../../resources/video.mp4' ) )
            ] );

            $response->assertJsonStructure( [
                'id',
                'path',
                'file',
                'extension',
                'options'
            ] );
            $data = json_decode( $response->content() );
            $this->assertDatabaseHas( ResourceModel::class, [
                'id' => $data->id
            ] );

            $this->assertFileExists( $this->storage->path( $data->path . '/' . $data->hls ) );

            $this->assertTrue( $this->resources->delete( $data->id ) );
        }
    }
