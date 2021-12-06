<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Str;

    class MakeEntityCommand extends Command {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'make:entity {name} {--jsonapi}';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Create model, migration, factory, seeder and policy';

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
         * @return int
         */
        public function handle() {
            $name = ucfirst( $this->argument( 'name' ) );

            $this->call( 'make:model', [
                'name' => $name,
                '-f'   => true,
                '-m'   => true,
                '-s'   => true
            ] ); // 4 actions

            $this->call( 'make:policy', [
                'name' => $name . 'Policy',
                '-m'   => $name
            ] ); // 1 action

            if ( $this->option( 'jsonapi' ) ) {
                $this->call( 'jsonapi:all', [
                    'name' => Str::plural( Str::lower( $name ) )
                ] ); // 5 actions
            }

            $this->newLine();
            $this->info( "Entity created successfully!" );

            return 1;
        }
    }
