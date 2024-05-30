<?php

namespace Database\Seeders;

use App\Models\PointOfSALE\ClothingCategory;
use App\Models\PointOfSALE\ClothingSize;
use App\Models\PointOfSale\PosProductSubcategory;
use App\Models\PointOfSale\PosUnitMeasurement;
use App\Models\PointOfSale\WarehouseProductCatalog;
use App\Models\User;
use Illuminate\Database\Seeder;

class WarehouseProductCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* 
        * Unidad de medida (mililitros)
        */
        $unitMililitrosId = PosUnitMeasurement::where('name', 'mililitros')->first()->id;
        /* 
        * Unidad de medida (gramos)
        */
        $unitGramosId = PosUnitMeasurement::where('name', 'gramos')->first()->id;
        /* 
        * Unidad de medida (unidades)
        */
        $unitUnidadesId = PosUnitMeasurement::where('name', 'unidades')->first()->id;
        /* 
        * User seller
        */
        $userSellerId = User::where('correo', 'root@gmail.com')->first()->id;
        /* 
        * Categorias de ropa
        */
        $clothingCategoryFemeninoId = ClothingCategory::where('name', 'femenino')->first()->id;
        $clothingCategoryMasculinoId = ClothingCategory::where('name', 'masculino')->first()->id;
        $clothingCategoryUnisexId = ClothingCategory::where('name', 'unisex')->first()->id;

        /* 
        * Productos para la subcategoria de refrescos
        */
        $cocaColaRegular = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitMililitrosId,
            'user_seller_id' => $userSellerId,
            'name' => 'coca cola regular',
            'unit_measurement_quantity' => 355,
            'description' => 'Refresco de cola regular',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000001'
        ]);

        $sidralMundet = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitMililitrosId,
            'user_seller_id' => $userSellerId,
            'name' => 'sidral mundet',
            'unit_measurement_quantity' => 355,
            'description' => 'Refresco de manzana',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000002'
        ]);

        $sprite = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitMililitrosId,
            'user_seller_id' => $userSellerId,
            'name' => 'sprite',
            'unit_measurement_quantity' => 355,
            'description' => 'Refresco de limon',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000003'
        ]);

        /* 
        *  Creamos la relacion de los productos con la subcategoria de refrescos
        */
        $refrescosSubcategory = PosProductSubcategory::where('name', 'refrescos')->first();
        $refrescosSubcategory->warehouse_product_catalogs()->attach([$cocaColaRegular->id, $sidralMundet->id, $sprite->id]);

        /* 
        * Productos para la subcategoria de cervezas
        */
        $corona = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitMililitrosId,
            'user_seller_id' => $userSellerId,
            'name' => 'corona',
            'unit_measurement_quantity' => 355,
            'description' => 'Cerveza clara',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000004'
        ]);

        $modeloEspecial = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitMililitrosId,
            'user_seller_id' => $userSellerId,
            'name' => 'modelo especial',
            'unit_measurement_quantity' => 355,
            'description' => 'Cerveza clara',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000005'
        ]);

        $indio = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitMililitrosId,
            'user_seller_id' => $userSellerId,
            'name' => 'indio',
            'unit_measurement_quantity' => 355,
            'description' => 'Cerveza clara',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000006'
        ]);

        /* 
        * Creamos la relacion de los productos con la subcategoria de cervezas
        */
        $cervezasSubcategory = PosProductSubcategory::where('name', 'cervezas')->first();
        $cervezasSubcategory->warehouse_product_catalogs()->attach([$corona->id, $modeloEspecial->id, $indio->id]);

        /* 
        * Productos para la subcategoria de agua
        */
        $bonafont = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitMililitrosId,
            'user_seller_id' => $userSellerId,
            'name' => 'bonafont',
            'unit_measurement_quantity' => 600,
            'description' => 'Agua embotellada',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000007'
        ]);

        $ciel = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitMililitrosId,
            'user_seller_id' => $userSellerId,
            'name' => 'ciel',
            'unit_measurement_quantity' => 600,
            'description' => 'Agua embotellada',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000008'
        ]);

        $epura = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitMililitrosId,
            'user_seller_id' => $userSellerId,
            'name' => 'epura',
            'unit_measurement_quantity' => 600,
            'description' => 'Agua embotellada',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000009'
        ]);

        /* 
        * Creamos la relacion de los productos con la subcategoria de agua
        */
        $aguaSubcategory = PosProductSubcategory::where('name', 'agua')->first();
        $aguaSubcategory->warehouse_product_catalogs()->attach([$bonafont->id, $ciel->id, $epura->id]);

        /* 
        * Productos para la subcategoria de snacks
        */
        $sabritas = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitGramosId,
            'user_seller_id' => $userSellerId,
            'name' => 'sabritas',
            'unit_measurement_quantity' => 50,
            'description' => 'Papas fritas',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000010'
        ]);

        $barritasMarinela = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitGramosId,
            'user_seller_id' => $userSellerId,
            'name' => 'barritas marinela',
            'unit_measurement_quantity' => 50,
            'description' => 'Barritas de chocolate',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000011'
        ]);

        $paletaPayaso = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'paleta payaso',
            'unit_measurement_quantity' => 1,
            'description' => 'Paleta de chocolate',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000012'
        ]);

        /* 
        * Creamos la relacion de los productos con la subcategoria de snacks
        */
        $snacksSubcategory = PosProductSubcategory::where('name', 'snacks')->first();
        $snacksSubcategory->warehouse_product_catalogs()->attach([$sabritas->id, $barritasMarinela->id, $paletaPayaso->id]);

        /* 
        * Productos para la subcategoria de comida rapida
        */
        $hamburguesa = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'hamburguesa',
            'unit_measurement_quantity' => 1,
            'description' => 'Hamburguesa de carne',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000013'
        ]);

        $hotDog = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'hot dog',
            'unit_measurement_quantity' => 1,
            'description' => 'Hot dog de salchicha',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000014'
        ]);

        $papasFritas = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitGramosId,
            'user_seller_id' => $userSellerId,
            'name' => 'papas fritas',
            'unit_measurement_quantity' => 50,
            'description' => 'Papas fritas',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000015'
        ]);

        /*
        * Creamos la relacion de los productos con la subcategoria de comida rapida y snacks
        */
        $comidaRapidaSubcategory = PosProductSubcategory::where('name', 'comida_rapida')->first();
        $comidaRapidaSubcategory->warehouse_product_catalogs()->attach([$hamburguesa->id, $hotDog->id, $papasFritas->id, $sabritas->id, $barritasMarinela->id, $paletaPayaso->id]);
        $snacksSubcategory->warehouse_product_catalogs()->attach([$hamburguesa->id, $hotDog->id, $papasFritas->id]);

        /* 
        * Productos para la subcategoria de postres, comida rapida y snacks
        */
        $pastelChocolate = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'pastel de chocolate',
            'unit_measurement_quantity' => 1,
            'description' => 'Pastel de chocolate',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000016'
        ]);

        $pastelVainilla = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'pastel de vainilla',
            'unit_measurement_quantity' => 1,
            'description' => 'Pastel de vainilla',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000017'
        ]);

        $pastelFresa = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'pastel de fresa',
            'unit_measurement_quantity' => 1,
            'description' => 'Pastel de fresa',
            'is_active' => true,
            'is_clothing' => false,
            'sales_code' => 'SC-000000000018'
        ]);

        /* 
        * Creamos la relacion de los productos con la subcategoria de postres
        */
        $postresSubcategory = PosProductSubcategory::where('name', 'postres')->first();
        $postresSubcategory->warehouse_product_catalogs()->attach([$pastelChocolate->id, $pastelVainilla->id, $pastelFresa->id]);
        $comidaRapidaSubcategory->warehouse_product_catalogs()->attach([$pastelChocolate->id, $pastelVainilla->id, $pastelFresa->id]);
        $snacksSubcategory->warehouse_product_catalogs()->attach([$pastelChocolate->id, $pastelVainilla->id, $pastelFresa->id]);

        /* 
        * Productos para la subcategoria de ropa
        */
        

        /* 
        * Productos para la subcategoria de accesorios
        */
        $llavero = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'llavero generico',
            'unit_measurement_quantity' => 1,
            'description' => 'Llavero generico',
            'is_active' => true,
            'is_clothing' => true,
            'sales_code' => 'SC-000000000019'
        ]);

        $pin = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'pin generico',
            'unit_measurement_quantity' => 1,
            'description' => 'Pin generico',
            'is_active' => true,
            'is_clothing' => true,
            'sales_code' => 'SC-000000000020'
        ]);

        $collar = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'collar generico',
            'unit_measurement_quantity' => 1,
            'description' => 'Collar generico',
            'is_active' => true,
            'is_clothing' => true,
            'sales_code' => 'SC-000000000021'
        ]);

        /* 
        * Creamos la relacion de los productos con la subcategoria de accesorios y ropa
        */
        $accesoriosSubcategory = PosProductSubcategory::where('name', 'accesorios')->first();
        $accesoriosSubcategory->warehouse_product_catalogs()->attach([$llavero->id, $pin->id, $collar->id]);

        /* 
        * Productos para la subcategoria de articulos_conmemorativos y accesorios
        */
        $poster = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'poster generico',
            'unit_measurement_quantity' => 1,
            'description' => 'Poster generico',
            'is_active' => true,
            'is_clothing' => true,
            'sales_code' => 'SC-000000000022'
        ]);

        $tarjeta = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'tarjeta generica',
            'unit_measurement_quantity' => 1,
            'description' => 'Tarjeta generica',
            'is_active' => true,
            'is_clothing' => true,
            'sales_code' => 'SC-000000000023'
        ]);

        $calcomania = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'name' => 'calcomania generica',
            'unit_measurement_quantity' => 1,
            'description' => 'Calcomania generica',
            'is_active' => true,
            'is_clothing' => true,
            'sales_code' => 'SC-000000000024'
        ]);

        /* 
        * Creamos la relacion de los productos con la subcategoria de articulos_conmemorativos
        */
        $articulosConmemorativosSubcategory = PosProductSubcategory::where('name', 'articulos_conmemorativos')->first();
        $articulosConmemorativosSubcategory->warehouse_product_catalogs()->attach([$poster->id, $tarjeta->id, $calcomania->id]);
        $accesoriosSubcategory->warehouse_product_catalogs()->attach([$poster->id, $tarjeta->id, $calcomania->id]);

        /* 
        * Productos para la subcategoria de chamarras, jerseys, bermudas
        */
        $chamarraNegra = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'clothing_category_id' => $clothingCategoryMasculinoId,
            'name' => 'chamarra negra',
            'unit_measurement_quantity' => 1,
            'description' => 'Chamarra negra de algodon',
            'is_active' => true,
            'is_clothing' => true,
            'sales_code' => 'SC-000000000025'
        ]);

        $jerseyAzul = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'clothing_category_id' => $clothingCategoryFemeninoId,
            'name' => 'jersey azul',
            'unit_measurement_quantity' => 1,
            'description' => 'Jersey azul de algodon',
            'is_active' => true,
            'is_clothing' => true,
            'sales_code' => 'SC-000000000026'
        ]);

        $bermudaNegra = WarehouseProductCatalog::create([
            'pos_unit_measurement_id' => $unitUnidadesId,
            'user_seller_id' => $userSellerId,
            'clothing_category_id' => $clothingCategoryUnisexId,
            'name' => 'bermuda negra',
            'unit_measurement_quantity' => 1,
            'description' => 'Bermuda negra de algodon',
            'is_active' => true,
            'is_clothing' => true,
            'sales_code' => 'SC-000000000027'   
        ]);

        /* 
        * Creamos la relacion de los productos con la subcategoria de chamarras, jerseys, bermudas
        */
        $chamarrasSubcategory = PosProductSubcategory::where('name', 'chamarras')->first();
        $chamarrasSubcategory->warehouse_product_catalogs()->attach([$chamarraNegra->id]);
        $jerseysSubcategory = PosProductSubcategory::where('name', 'jerseys')->first();
        $jerseysSubcategory->warehouse_product_catalogs()->attach([$jerseyAzul->id]);
        $bermudasSubcategory = PosProductSubcategory::where('name', 'bermudas')->first();
        $bermudasSubcategory->warehouse_product_catalogs()->attach([$bermudaNegra->id]);

    }
}
