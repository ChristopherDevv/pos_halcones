<?php

namespace Database\Seeders;

use App\Models\PointOfSale\PosProductCategory;
use App\Models\PointOfSale\PosProductSubcategory;
use Illuminate\Database\Seeder;

class PosProductSubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* 
        * Subcategorias para la categoria de bebidas
        */
        $refrescos = PosProductSubcategory::create([
            'name' => 'refrescos',
            'description' => 'Refrescos de todo tipo',
            'image_file' => null,
        ]);

        $cervezas = PosProductSubcategory::create([
            'name' => 'cervezas',
            'description' => 'Cervezas de todo tipo',
            'image_file' => null,
        ]);

        $agua = PosProductSubcategory::create([
            'name' => 'agua',
            'description' => 'Agua embotellada de todo tipo',
            'image_file' => null,
        ]);

        /* 
        * Creamos la relacion de las subcategorias con las categorias de bebidas
        */
        $bebidasCategory = PosProductCategory::where('name', 'bebidas')->first();
        $bebidasCategory->pos_product_subcategories()->attach([$refrescos->id, $cervezas->id, $agua->id]);

        /* 
        * Subcategorias para la categoria de comidas
        */
        $snacks = PosProductSubcategory::create([
            'name' => 'snacks',
            'description' => 'Snacks de todo tipo',
            'image_file' => null,
        ]);

        $comidaRapida = PosProductSubcategory::create([
            'name' => 'comida_rapida',
            'description' => 'Comida rapida de todo tipo',
            'image_file' => null,
        ]);

        $postres = PosProductSubcategory::create([
            'name' => 'postres',
            'description' => 'Postres de todo tipo',
            'image_file' => null,
        ]);

        /* 
        * Creamos la relacion de las subcategorias con las categorias de comidas
        */
        $comidasCategory = PosProductCategory::where('name', 'comidas')->first();
        $comidasCategory->pos_product_subcategories()->attach([$snacks->id, $comidaRapida->id, $postres->id]);

        /* 
        * Subcategorias para la categoria de merch
        */
        $chamarras = PosProductSubcategory::create([
            'name' => 'chamarras',
            'description' => 'Chamarras de todo tipo',
            'image_file' => null,
        ]);

        $jerseys = PosProductSubcategory::create([
            'name' => 'jerseys',
            'description' => 'Jerseys de todo tipo',
            'image_file' => null,
        ]);

        $bermudas = PosProductSubcategory::create([
            'name' => 'bermudas',
            'description' => 'Bermudas de todo tipo',
            'image_file' => null,
        ]);

        $polos = PosProductSubcategory::create([
            'name' => 'polos',
            'description' => 'Polos de todo tipo',
            'image_file' => null,
        ]);

        $playeras = PosProductSubcategory::create([
            'name' => 'playeras',
            'description' => 'Playeras de todo tipo',
            'image_file' => null,
        ]);

        $accesorios = PosProductSubcategory::create([
            'name' => 'accesorios',
            'description' => 'Llaveros, pins, etc',
            'image_file' => null,
        ]);

        $articulosConmemorativos = PosProductSubcategory::create([
            'name' => 'articulos_conmemorativos',
            'description' => 'Tarjetas, posters, etc',
            'image_file' => null,
        ]);

        /* 
        * Creamos la relacion de las subcategorias con las categorias de merch
        */
        $merchCategory = PosProductCategory::where('name', 'merch')->first();
        $merchCategory->pos_product_subcategories()->attach([
            $chamarras->id, 
            $accesorios->id, 
            $articulosConmemorativos->id,
            $jerseys->id,
            $bermudas->id,
            $polos->id,
            $playeras->id
        ]);

    }
}
