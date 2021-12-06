<?php

    use Nila\Payments\Models\Wallet;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateBankAccountsTable extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up() {
            Schema::create( 'bank_accounts', function( Blueprint $table ) {
                $table->id();
                $table->foreignIdFor( Wallet::class )->constrained();
                $table->string( 'address', 50 );
                $table->string( 'holder', 512 );
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down() {
            Schema::dropIfExists( 'bank_accounts' );
        }
    }
