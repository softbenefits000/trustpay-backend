<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'role_id' => 1,
            'firstname' => 'Test',
            'lastname' => 'User',
            'phone_number' => '08020000000',
            'email' => 'test@trustpay.com',
            'password' => app('hash')->make('test123'),
            'PIN' => app('hash')->make('1234'),
            'BVN' => app('hash')->make('22241999182931'),
            'ip_address' => '127.0.0.1',
            'device_type' => 'web browser',
            'device_version' => '41.0.1',
            'remember_token' => str_random(10),
        ]);
    }
}
