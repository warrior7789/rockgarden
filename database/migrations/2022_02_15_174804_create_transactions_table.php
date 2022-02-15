<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('invoice_id')->nullable();
            $table->integer('customer_user_id')->nullable();
            $table->string('customer_email',765)->nullable();
            $table->string('payment_name',765)->nullable();
            $table->double('amount',16,2)->nullable();

            $table->string('currency',765)->nullable();
            $table->string('authorization_url',765)->nullable();
            $table->string('access_code',765)->nullable();
            $table->string('reference',765)->nullable();
            $table->string('status',765)->nullable();
            $table->string('gateway_response',765)->nullable();
            $table->tinyInteger('charge_attempted',4)->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->tinyInteger('save_card_auth',4)->nullable();
            $table->tinyInteger('is_flutterwave',4)->nullable();
            $table->string('link',4)->nullable();
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
        Schema::dropIfExists('transactions');
    }
}