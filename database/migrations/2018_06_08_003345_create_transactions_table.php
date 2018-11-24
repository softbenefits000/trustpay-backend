<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->integer('type');
            $table->integer('customer_id');
            $table->integer('beneficiary_merchant_id');
            $table->string('order_code', 10);
            $table->integer('amount_payed');
            $table->integer('response_code');
            $table->text('response_description');
            $table->integer('delivery_man');
            $table->string('delivery_location', 255);
            $table->integer('product_delivery_status');
            $table->dateTime('transaction_date');
            $table->date('delivery_date');
            $table->integer('status');
            $table->softDeletes();
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
