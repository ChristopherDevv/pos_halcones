<?php

namespace Database\Seeders;

use App\Models\PointOfSale\ProductsForBucketvendor;
use Illuminate\Database\Seeder;

class ProductsForBucketvendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductsForBucketvendor::create([
            'bucketvendor_name' => 'vendor',
            'bucketvendor_last_name' => 'generico',
            'bucketvendor_phone' => '1234567890',
            'is_active' => true
        ]);
    }
}
