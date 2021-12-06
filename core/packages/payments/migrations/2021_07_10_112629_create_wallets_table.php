<?php

    use App\Models\User;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateWalletsTable extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up() {
            Schema::create( 'wallets', function( Blueprint $table ) {
                $table->id();
                $table->foreignIdFor( User::class )->constrained();
                $table->unsignedBigInteger( 'balance' )->default( 0 );
                $table->boolean( 'active' )->default( true );
                $table->string( 'currency', 512 );
                $table->string( 'version', 256 )->default('0');
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down() {
            Schema::dropIfExists( 'wallets' );
        }
    }
