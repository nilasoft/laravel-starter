<?php

    namespace Tests\Feature\Menus;

    use Nila\Menus\Models\Menu as MenuModel;
    use Tests\TestCase;

    class MenusContractTest extends TestCase {
        /**
         * @test
         *
         *
         * @return void
         */
        public function find() {
            $key = $this->menusConfig[ 'menus' ][ 0 ][ 'key' ] ?? 'specify a key';
            $this->assertTrue( MenuModel::where( 'key', $key )->exists() );
            $this->assertTrue( $this->menus->find( $key )->contains( 'id', MenuModel::firstWhere( 'key', $key )->id ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function findAny() {
            $firstKey  = $this->menusConfig[ 'menus' ][ 0 ][ 'key' ] ?? 'specify a key';
            $secondKey = $this->menusConfig[ 'menus' ][ 1 ][ 'key' ] ?? 'specify another key';

            $this->assertTrue( MenuModel::where( 'key', $firstKey )->exists() );
            $this->assertTrue( MenuModel::where( 'key', $secondKey )->exists() );

            $this->assertTrue( $this->menus->findAny( $firstKey, $secondKey )
                                           ->first()
                                           ->contains( 'id', MenuModel::firstWhere( 'key', $firstKey )->id ) );

            $this->assertTrue( $this->menus->findAny( $firstKey, $secondKey )
                                           ->last()
                                           ->contains( 'id', MenuModel::firstWhere( 'key', $secondKey )->id ) );
        }

        /**
         * @test
         *
         *
         * @return void
         */
        public function findAll() {
            $this->assertTrue( collect( MenuModel::where( 'key', null )->get() )->every( function( MenuModel $item ) {
                return $this->menus->findAll()->transform( function( MenuModel $item ) {
                    return $this->transform( $item );
                } )->flatten()->contains( 'id', $item->id );
            } ) );
        }
    }
