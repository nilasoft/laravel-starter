<?php

    namespace Tests\Feature\Resources;

    use Illuminate\Http\UploadedFile;
    use Nila\Resources\Models\Resource as ResourceModel;
    use Tests\TestCase;

    class BatchUploadTest extends TestCase {
        /**
         * @test
         *
         *
         * @return void
         */
        public function batchUpload() {
            //$this->withoutExceptionHandling();
            $response = $this->postJson( route( 'resources.test.batch', [ 'field' => 'batchUpload' ] ), [
                'batchUpload' => [
                    //UploadedFile::fake()->create( 'video.mp4', 10230,'video/mp4' ),
                    UploadedFile::fake()->image( 'imagefile.png', 512, 512 ),
                    UploadedFile::fake()->create( 'ziped.zip', 10230, 'application/zip' ),
                    $link = 'http://laravel.com/img/homepage/vapor.jpg',
                ],
            ] );
            $response->assertCreated()->assertJsonStructure( [
                [
                    'id',
                    'path',
                    'file',
                    'extension',
                    'options'
                ]
            ] );
            $data = json_decode( $response->content() );
            collect( $data )->each( function( $item ) {
                match ( $item->external ) {
                    true => $this->checkExternals( $item ),
                    false => $this->checkFiles( $item )
                };
            } );

        }

        private function checkFiles( object $item ): void {
            $model = ResourceModel::findOrFail( $item->id );
            $this->assertDatabaseHas( ResourceModel::class, [
                'title'     => $item->title,
                'path'      => $item->path,
                'external'  => false,
                'file'      => $item->file,
                'extension' => $item->extension
            ] );
            $this->assertDirectoryExists( $this->storage->path( $item->path ) );
            $this->assertEquals( $item->path . '/' . $item->file, $model->address );
            $this->assertFileExists( $this->storage->path( $model->address ) );

            $this->assertTrue( $this->resources->delete( $model->id ) );
        }

        private function checkExternals( object $item ): void {
            $model = ResourceModel::findOrFail( $item->id );
            $this->assertDatabaseHas( ResourceModel::class, [
                'title'     => $item->title,
                'path'      => $item->path,
                'external'  => true,
                'file'      => $item->file,
                'extension' => $item->extension
            ] );
            $this->assertTrue( $this->resources->delete( $model->id ) );
        }
    }
