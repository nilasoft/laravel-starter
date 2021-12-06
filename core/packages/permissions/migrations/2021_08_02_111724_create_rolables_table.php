<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateRolablesTable extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up() {
            Schema::create( 'rolables', function( Blueprint $table ) {
                $table->foreignId( 'role_id' )->constrained();
                $table->unsignedBigInteger( 'rolable_id' );
                $table->string( 'rolable_type' );
            } );
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down() {
            Schema::dropIfExists( 'rolables' );
        }
    }
