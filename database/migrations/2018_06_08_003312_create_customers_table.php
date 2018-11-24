<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('phone_number', 15);
            $table->string('email')->unique();
            $table->string('password', 60);  
            $table->string('PIN', 60);
            $table->string('confirmation_token', 60);
            $table->integer('status')->default(0);
            $table->string('ip_address', 45);
            $table->string('device_type', 40);
            $table->string('device_version', 40);
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
        Schema::dropIfExists('customers');
    }
}
