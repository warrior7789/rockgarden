<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('service_application_id')->nullable();
            $table->string('payment_name')->nullable();
            $table->string('payment_description')->nullable();
            $table->float('payment_amount',16,2)->nullable();
            $table->string('currency')->nullable();
            $table->tinyInteger('is_paid')->nullable();
            $table->integer('paid_by_user_id')->nullable();
            $table->date('due_date')->nullable();
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
        Schema::dropIfExists('invoices');
    }
}
