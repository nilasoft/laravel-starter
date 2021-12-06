<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Symfony\Component\Console\Input\InputOption;

    class JsonApiGenerateAllFilesCommand extends Command {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'jsonapi:all {name} {--p|proxy} {--force}';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Create all the schema files';

        /**
         * Create a new command instance.
         *
         * @return void
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * Execute the console command.
         *
         * @return void
         */
        public function handle() {
            $args = collect( [
                'name'    => $this->argument( 'name' ),
                '--force' => $this->option( 'force' ),
            ] )->reject( fn( $value ) => is_null( $value ) )->all();

            $this->call( 'jsonapi:schema', array_merge( $args, [
                '--model'  => $this->anticipate( 'what is the related model?', $this->getModels() ),
                '--server' => $this->anticipate( 'which server?', array_keys( config( 'jsonapi.servers' ) ) ),
                '--proxy'  => $this->option( 'proxy' ),
            ] ) );
            $this->call( 'jsonapi:resource', $args );
            $this->call( 'jsonapi:request', $args );
            $this->call( 'jsonapi:query', array_merge( $args, [ '--both' => true ] ) );
        }

        private function getModels( string $directory = '', string $namespace = '' ): array {
            $folder = ( $namespace ? $namespace . '/' : 'app/Models/' ) . $directory;
            try {
                $dir = scandir( $folder );
            } catch ( \Throwable $e ) {
                $dir = [];
            }

            foreach ( $dir as $item ) :
                if ( $item == '.' || $item == '..' ) {
                    continue;
                }
                if ( str_ends_with( $item, 'php' ) ) {
                    $models[] = substr( $item, 0, - 4 );
                } else {
                    $models[] = $this->getModels( $item, $folder );
                }
            endforeach;

            return collect( $models ?? [] )->flatten()->toArray();
        }

    }
