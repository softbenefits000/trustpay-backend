<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id');
            $table->integer('seller_id')->nullable();
            $table->integer('deliveryman_id')->nullable();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('phone_number', 15)->unique();
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->string('PIN', 60);
            $table->string('BVN', 60)->nullable();
            $table->integer('verified')->default(0);
            $table->string('ip_address', 45);
            $table->string('device_type', 40);
            $table->string('device_version', 40);
            $table->string('confirmation_token');
            $table->integer('status')->default(0);
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
