<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	class CreateMenusTable extends Migration {
		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up() {
			Schema::create( 'menus', function( Blueprint $table ) {
				$table->id();

				$table->integer( 'order' )->default( 1 );
				$table->unsignedBigInteger( 'parent_id' )->nullable();

				$table->string( 'title' );
				$table->string( 'icon' )->nullable();
				$table->string( 'class' )->nullable();
				$table->string( 'link' );

				$table->string( 'key' );
				$table->timestamps();
			} );
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down() {
			Schema::dropIfExists( 'menus' );
		}
	}
