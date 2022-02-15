<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardAuthorizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_authorizations', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('authorization_code',765)->nullable();
            $table->string('bin',765)->nullable();
            $table->string('last4',765)->nullable();
            $table->string('exp_month',765)->nullable();
            $table->string('exp_year',765)->nullable();
            $table->string('channel',765)->nullable();
            $table->string('card_type',765)->nullable();
            $table->string('bank',765)->nullable();
            $table->string('country_code',765)->nullable();
            $table->string('brand',765)->nullable();
            $table->string('signature',765)->nullable();
            $table->string('account_name',765)->nullable();
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
        Schema::dropIfExists('card_authorizations');
    }
}
