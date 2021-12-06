<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    use Nila\Payments\Models\Requests\TransactionRequest;
    use Nila\Payments\Models\Wallet;

    class CreateTransactionsTable extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up() {
            Schema::create( 'transactions', function( Blueprint $table ) {
                $table->id();
                $table->foreignIdFor( Wallet::class )->constrained();
                $table->foreignIdFor( TransactionRequest::class )->nullable()->constrained();
                $table->unsignedBigInteger( 'amount' );
                $table->string( 'type', 256 )->comment( 'deposit,withdraw,purchase' );
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down() {
            Schema::dropIfExists( 'transactions' );
        }
    }
