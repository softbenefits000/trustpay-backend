<?php

use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customers')->insert([	
            'firstname' => 'Tom',
            'lastname' => 'test',
            'phone_number' => '08020000000',
            'email' => 'customer@trustpay.com',
            'password' => app('hash')->make('test123'),
            'PIN' => app('hash')->make('1234'),
            'ip_address' => '127.0.0.1',
            'device_type' => 'web browser',
            'device_version' => '41.0.1'
        ]);
    }
}
