<?php

    namespace Tests\Unit\Menus;

    use Nila\Menus\Models\Menu as MenuModel;
    use Tests\TestCase;

    class MenuModelTest extends TestCase {
        /**
         * @test
         *
         *
         * @return void
         */
        public function generateMenu() {
            $generated = MenuModel::generate()->get();
            $this->assertArrayHasKey( 'descendants', $generated->first()->toArray() );
            $this->assertNull( $generated->first()->parent_id );

            $key       = $this->menusConfig[ 'menus' ][ 0 ][ 'key' ] ?? 'specify a key';
            $generated = MenuModel::generate( $key )->get();
            $this->assertArrayHasKey( 'key', $generated->first()->toArray() );
            $this->assertEquals( $key, $generated->first()->key );
        }
    }
