<?php


    namespace Nila\Menus\Console;


    use Nila\Menus\Models\Menu;
    use Illuminate\Console\Command;
    use Symfony\Component\Console\Helper\ProgressBar;

    class InstallCommand extends Command {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'menus:install {--fresh}';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Create all the menus according to its config file';

        /**
         * Create a new command instance.
         *
         * @return void
         */
        public function __construct() {
            parent::__construct();
        }

        public function handle() {
            $menus = config( 'menus.menus' );
            if ( $this->option( 'fresh' ) ) {
                foreach ( Menu::all() as $item ) {
                    $item->permissions()->sync( [] );
                }
                Menu::truncate();
            }

            $this->withProgressBar( collect( $menus )->map( function( $value ) {
                return $this->counter( $value );
            } )->sum(), function( ProgressBar $progress ) use ( $menus ) {
                foreach ( $menus as $key => $menu ) :
                    $this->create( $menu, $progress );
                endforeach;
                $progress->finish();
            } );
            $this->newLine();
        }

        private function create( array $menu, ProgressBar &$progress ) {
            $item = Menu::create( $menu );
            $item->syncPermissions( ...$menu[ 'permissions' ] );
            $progress->setProgress( $progress->getProgress() + 1 );
            if ( isset( $menu[ 'children' ] ) and is_array( $menu[ 'children' ] ) ) {
                foreach ( $menu[ 'children' ] as $child ) :
                    $this->create( array_merge( $child, [
                        'parent_id' => $item->id
                    ] ), $progress );
                endforeach;
            }
        }

        private function counter( array $item ): int {
            $counter = 1;
            if ( isset( $item[ 'children' ] ) and is_array( $item[ 'children' ] ) ) {
                foreach ( $item[ 'children' ] as $child ) :
                    $counter += $this->counter( $child );
                endforeach;
            }

            return $counter;
        }

    }
