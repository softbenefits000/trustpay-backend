<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryMenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_men', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type');
            $table->string('business_name');
            $table->string('business_email')->unique();
            $table->string('business_phone', 15);
            $table->string('business_address');
            $table->string('business_city');
            $table->string('business_state');
            $table->string('business_country');
            $table->integer('added_by');
            $table->string('siteURL');
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
        Schema::dropIfExists('delivery_men');
    }
}
