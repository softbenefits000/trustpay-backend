<?php

use Illuminate\Database\Seeder;

class TransactionStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        //transaction status

        $status = array(
            array (
                'title' => 'Pending',
                'description' => 'Orders placed and paid for into escrow'
            ),
            array (
                'title' => 'Accepted',
                'description' => 'Orders accepted by buyer and Seller is credited'
            ),
            array (
                'title' => 'Rejected',
                'description' => 'Orders rejected by buyer and Buyer is refunded',

            ),
            array (
                'title' => 'Disputed',
                'description' => 'Orders rejected by Buyer and Buyer/ Seller raise dispute',
            ),
            array (
                'title' => 'Complete',
                'description' => 'Orders  where Buyer is refunded after rejection or Seller is credited after Buyer acceptance or Buyer/ Seller is credited after a dispute settlement'
            ),
            array (
                'title' => 'Created',
                'description' => 'Orders placed and Order code generated. Payment has not been made'
            )
        );
        DB::table('transaction_statuses')->insert($status);
    }
}
