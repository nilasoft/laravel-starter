<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreatePermissionablesTable extends Migration {

        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up() {
            Schema::create( 'permissionables', function( Blueprint $table ) {
                $table->foreignId( 'permission_id' )->constrained();
                $table->unsignedBigInteger( 'permissionable_id' );
                $table->string( 'permissionable_type' );
            } );
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down() {
            Schema::dropIfExists( 'permissionables' );
        }
    }
