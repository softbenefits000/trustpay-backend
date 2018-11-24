<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('is_marketplace')->default(0);
            $table->integer('marketplace_child')->nullable(0);
            $table->string('business_name')->unique();
            $table->string('business_address', 50);
            $table->string('business_address_2', 50)->nullable();
            $table->string('business_city', 50);
            $table->string('business_state', 50);
            $table->string('business_country', 50);
            $table->string('business_email',50)->unique();
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
        Schema::dropIfExists('sellers');
    }
}
