<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nila\Payments\Models\Requests\BankAccount;
use Nila\Payments\Models\Wallet;

class CreateTransactionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Wallet::class)->constrained();
            $table->foreignIdFor(BankAccount::class)->nullable()->constrained();
            $table->unsignedBigInteger('amount')->nullable();
            $table->unsignedBigInteger('amount_request');
            $table->string('gateway', 512)->nullable();
            $table->string('type', 256)->comment('deposit,withdraw,purchase,deposit_manual,withdraw_manual');
            $table->string('status', 256);
            $table->string('extra')->nullable();
            $table->string('purchasable_type')->nullable();
            $table->unsignedBigInteger('purchasable_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_requests');
    }
}
