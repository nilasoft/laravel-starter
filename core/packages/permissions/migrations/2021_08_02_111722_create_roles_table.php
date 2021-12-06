<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateRolesTable extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up() {
            Schema::create( 'roles', function( Blueprint $table ) {
                $table->id();
                $table->string( 'name', 128 );
                $table->string( 'area', 128 );

                $table->unique( [ 'name', 'area' ] );
                $table->unsignedInteger( 'version' )->default( 1 );
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down() {
            Schema::dropIfExists( 'roles' );
        }
    }
