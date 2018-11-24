<?php

use Illuminate\Database\Seeder;

class DeliveryMenTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $types = array(
            array (
                'title' => 'Independent Dispatcher',
                'description' => 'Sole Dispatcher that receives and delivers goods'
            ),
            array (
                'title' => 'Staff (Dispatcher) of Seller',
                'description' => 'A staff of the seller/marketplace that registers him/her'
            ),
            array (
                'title' => 'Staff(Dispatcher) of Courier Business',
                'description' => 'A staff of a registered Courier Business Company',

            )
        );
        DB::table('delivery_men_types')->insert($types);
    }
}
