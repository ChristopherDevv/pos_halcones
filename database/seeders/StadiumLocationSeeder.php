<?php

namespace Database\Seeders;

use App\Models\PointOfSale\StadiumLocation;
use Illuminate\Database\Seeder;

class StadiumLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StadiumLocation::create([
            'name' => 'el nido del halcon',
            'description' => 'estadio halcones 1',
            'address' => 'cultura veracruzana, zona universitaria, campus cad',
            'city' => 'xalapa enriquez',
            'state' => 'veracruz',
            'country' => 'mexico',
            'zip_code' => '91094',
            'phone' => '0000000000',
            'email' => 'soporte_hdx@halconesdexalapa.com.mx',
        ]);
    }
}
