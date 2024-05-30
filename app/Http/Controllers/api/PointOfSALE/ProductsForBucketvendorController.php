<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\ProductsForBucketvendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsForBucketvendorController extends Controller
{

    /* 
    *
    * Get all ProductsForBucketvendor by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            /* 
            * obtener todos los vendedores activos
            */
            $productsForBucketvendor = ProductsForBucketvendor::where('is_active', true)->get();

            return response()->json([
                'message' => 'ProductsForBucketvendor get successfully',
                'data' => $productsForBucketvendor
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, do not get the ProductsForBucketvendor',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * 
    * Create a new ProductsForBucketvendor by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'bucketvendor_name' => 'required|string|max:255',
                'bucketvendor_last_name' => 'required|string|max:255',
                'bucketvendor_phone' => 'required|string|max:255',
            ]);

            /* 
            * formatera nombre y apellido
            */
            $bucketvendorName = str_replace(' ','_',strtolower($request->bucketvendor_name));
            $bucketvendorLastName = str_replace(' ','_',strtolower($request->bucketvendor_last_name));

            /* 
            * Validar que no exista un registro con el mismo nombre, apellido y telefono
            */
            $productsForBucketvendor = ProductsForBucketvendor::where('bucketvendor_name', $bucketvendorName)
                ->where('bucketvendor_last_name', $bucketvendorLastName)
                ->where('bucketvendor_phone', $request->bucketvendor_phone)
                ->where('is_active', true)
                ->first();

            if ($productsForBucketvendor) {
                return response()->json([
                    'message' => 'Error, el vendedor ya existe'
                ], 400);
            }

            /* 
            * Creamos una nueva instancia de ProductsForBucketvendor
            */
            $productsForBucketvendor = new ProductsForBucketvendor();
            $productsForBucketvendor->bucketvendor_name = $bucketvendorName;
            $productsForBucketvendor->bucketvendor_last_name = $bucketvendorLastName;
            $productsForBucketvendor->bucketvendor_phone = $request->bucketvendor_phone ?? null;
            $productsForBucketvendor->is_active = true;
            $productsForBucketvendor->save();

            DB::commit();

            return response()->json([
                'message' => 'ProductsForBucketvendor created successfully',
                'data' => $productsForBucketvendor
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, do not create the ProductsForBucketvendor',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Get all warehouse_product_inventories of a ProductsForBucketvendor by Christoper Patiño
    *
    */
    public function showProductsByBucketvendor(Request $request)
    {
        try {

            $request->validate([
                'products_for_bucketvendor_id' => 'required|integer',
                'close_sale' => 'required|boolean'
            ]);

            $productsForBucketvendor = ProductsForBucketvendor::where('id', $request->products_for_bucketvendor_id)->first();
            if (!$productsForBucketvendor) {
                return response()->json([
                    'message' => 'Error, products_for_bucketvendor_id does not exist.',
                    'data' => $productsForBucketvendor
                ], 400);
            }

            /* 
            * obtener todos los warehouse_product_inventories de un vendedor el dia actual
            */
            $warehouseProductInventories = $productsForBucketvendor->warehouse_product_inventories()
            ->wherePivot('created_at', '>=', now()->startOfDay())
            ->wherePivot('created_at', '<=', now()->endOfDay())
            ->get();

            /* 
            * Formatear la respuesta
            */
            $warehouseProductInventories = $warehouseProductInventories->map(function ($item) use ($productsForBucketvendor){
                return [
                    'id' => $item->id,
                    'name' => $item->warehouse_product_catalog->name,
                    'unit_measurement' => $item->warehouse_product_catalog->pos_unit_measurement->name,
                    'unit_measurement_abbr' => $item->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                    'unit_measurement_quantity' => $item->warehouse_product_catalog->unit_measurement_quantity,
                    'is_clothing' => $item->warehouse_product_catalog->is_clothing,
                    'sales_code' => $item->warehouse_product_catalog->sales_code,
                    'description' => $item->warehouse_product_catalog->description,
                    'quantity' => $item->pivot->quantity,
                    'sale_for_bucketvendor' => $item->discount_sale_price,
                    'amount_total' => $item->pivot->quantity * $item->discount_sale_price,
                    'images' => $item->warehouse_product_catalog->images->pluck('uri_path')->toArray() ?? 'No images found',
                    'created_at' => $item->pivot->created_at->format('Y-m-d H:i:s'),
                    'warehouse_name' => $item->pos_product_warehouse->name,
                    'manager_name' => $item->pos_product_warehouse->user_manager->nombre,
                    'bucketvendor_full_name' => $productsForBucketvendor->bucketvendor_name . ' ' . $productsForBucketvendor->bucketvendor_last_name,
                    'bucketvendor_phone' => $productsForBucketvendor->bucketvendor_phone,
                    'acknowledgment_key' => 'generic_acknowledgment_key',
                ];
            });


            if($request->close_sale){

                $pdf = PDF::loadView('pdfs.inventarios_globales.acuse_bucketvendors_ventas', [
                    'warehouseProductInventories' => $warehouseProductInventories,
                ]);

                $pdf->save(public_path('pdfs/ventas_del_vendedor' . $productsForBucketvendor->bucketvendor_name . '_' . $productsForBucketvendor->bucketvendor_last_name . '_' . now()->toDateString() . '.pdf'));
                return $pdf->stream();
            }

            return response()->json([
                'message' => 'WarehouseProductInventories get successfully',
                'data' => $warehouseProductInventories
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, do not get the warehouse_product_inventories',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Get all sales of a ProductsForBucketvendor by Christoper Patiño
    *
    */
    public function showSalesByBucketvendor(Request $request)
    {
        try {

            $request->validate([
                'products_for_bucketvendor_id' => 'required|integer',
            ]);

            $productsForBucketvendor = ProductsForBucketvendor::where('id', $request->products_for_bucketvendor_id)->first();
            if (!$productsForBucketvendor) {
                return response()->json([
                    'message' => 'Error, products_for_bucketvendor_id does not exist.',
                    'data' => $productsForBucketvendor
                ], 400);
            }

            /* 
            * Obtener todas las ventas de un vendedor del dia actual
            */
            $sales = $productsForBucketvendor->pos_sales()->where('created_at', '>=', now()->startOfDay())->where('created_at', '<=', now()->endOfDay())->get();
            /* 
            * Formatear la respuesta
            */
            $sumarySales = []; // 4   1,2,3
            foreach($sales as $sale) {
                $sumarySales[] = [
                    'id_ticket' => $sale->pos_tickets[0]->id,
                    'total_amount' => $sale->total_amount,
                    'is_bucketvendor_sale' => $sale->is_bucketvendor_sale,
                    'products_for_bucketvendor_full_name' => $sale->products_for_bucketvendor->bucketvendor_name . ' ' . $sale->products_for_bucketvendor->bucketvendor_last_name,
                    'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                    'products' => $sale->warehouse_product_inventories->map(function ($item){
                        return [
                            'id' => $item->id,
                            'name' => $item->warehouse_product_catalog->name,
                            'unit_measurement' => $item->warehouse_product_catalog->pos_unit_measurement->name,
                            'unit_measurement_abbr' => $item->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                            'unit_measurement_quantity' => $item->warehouse_product_catalog->unit_measurement_quantity,
                            'is_clothing' => $item->warehouse_product_catalog->is_clothing,
                            'sales_code' => $item->warehouse_product_catalog->sales_code,
                            'description' => $item->warehouse_product_catalog->description,
                            'quantity' => $item->pivot->quantity,
                            'sale_for_bucketvendor' => $item->discount_sale_price,
                            'amount_total' => $item->pivot->quantity * $item->discount_sale_price,
                            'images' => $item->warehouse_product_catalog->images->pluck('uri_path')->toArray() ?? 'No images found',
                            'created_at' => $item->pivot->created_at->format('Y-m-d H:i:s'),
                            'warehouse_name' => $item->pos_product_warehouse->name,
                            'manager_name' => $item->pos_product_warehouse->user_manager->nombre,
                            'acknowledgment_key' => 'generic_acknowledgment_key',
                        ];
                    })
                ];
            }


            return response()->json([
                'message' => 'Sales get successfully',
                'data' => $sumarySales
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, do not get the sales',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
