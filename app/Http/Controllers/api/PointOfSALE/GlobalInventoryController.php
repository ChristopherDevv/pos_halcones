<?php

namespace App\Http\Controllers\api\PointOfSALE;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\GlobalInventory;
use App\Models\PointOfSale\GlobalInventoryTransaction;
use App\Models\PointOfSale\InventoryTransactionType;
use App\Models\PointOfSale\PosProductWarehouse;
use App\Models\PointOfSale\StadiumLocation;
use App\Models\PointOfSale\WarehouseProductCatalog;
use App\Models\PointOfSale\WarehouseProductInventory;
use App\Models\PointOfSale\WarehouseTransactionAcknowledgment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalInventoryController extends Controller
{
    /* 
    *
    * Get all Global Inventory by Christoper Patiño
    *
    */
    public function index(Request $request) {
        try {
            $request->validate([
                'pos_product_warehouse_id' => 'nullable|numeric',
                'stadium_location_id' => 'nullable|numeric'
            ]);
    
            $global_inventory = GlobalInventory::where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
                ->where('stadium_location_id', $request->stadium_location_id)
                ->get();
    
            // Agrupar por código de venta y catalogar los productos similares
            $grouped = $global_inventory->groupBy(function($item) {
                return $item->warehouse_product_catalog->sales_code;
            });
    
            $formatted = $grouped->map(function($items, $sales_code) {
                // Tomar el primer elemento como base para los datos comunes
                $baseItem = $items->first();
    
                // Crear un arreglo para los datos variables
                $variableData = $items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'current_stock' => $item->current_stock,
                        'purchase_price' => $item->purchase_price,
                        'sale_price' => $item->sale_price,
                        'discount_sale_price' => $item->discount_sale_price,
                        'clothing_size' => $item->clothing_size->name ?? 'no aplica',
                    ];
                });
    
                // Retornar el objeto formateado
                return [
                    'warehouse_product_catalog' => $baseItem->warehouse_product_catalog->name,
                    'unit_measurement' => $baseItem->warehouse_product_catalog->pos_unit_measurement->name,
                    'unit_measurement_abbr' => $baseItem->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                    'unit_measurement_quantity' => $baseItem->warehouse_product_catalog->unit_measurement_quantity,
                    'is_clothing' => $baseItem->warehouse_product_catalog->is_clothing,
                    'is_active' => $baseItem->warehouse_product_catalog->is_active,
                    'sales_code' => $sales_code,
                    'description' => $baseItem->warehouse_product_catalog->description,
                    'clothing_category' => $baseItem->warehouse_product_catalog->clothing_category->name ?? 'no aplica',
                    'pos_product_subcategories' => $baseItem->warehouse_product_catalog->pos_product_subcategories->pluck('name'),
                    'stadium_location' => $baseItem->stadium_location->name,
                    'pos_product_warehouse' => $baseItem->pos_product_warehouse->name,
                    'primary_data' => $variableData,
                    'images' => $baseItem->warehouse_product_catalog->images->pluck('uri_path'),
                    'created_at' => $baseItem->created_at->format('Y-m-d H:i:s'),
                ];
            });
    
            return response()->json([
                'message' => 'All Global Inventory for this Stadium Location and POS Product Warehouse',
                'data' => $formatted->values()->all()
            ], 200);
    
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error, cannot get all Global Inventory',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create Global Inventory by Christoper Patiño
    *
    */
    public function storeTransactions(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'pos_product_warehouse_id' => 'nullable|numeric',
                'global_inventory_id' => 'nullable|numeric',
                'global_inventories' => 'nullable|array',
                'stadium_location_id' => 'nullable|numeric',
                'product_data' => 'nullable|array',
                'warehouse_transaction_acknowledgment_id' => 'required|numeric',
                'reason' => 'nullable|string',
                'purchase_price' => 'nullable|numeric',
                'sale_price' => 'nullable|numeric',
                'discount_sale_price' => 'nullable|numeric'
            ]);

            /* 
            * Validacion de datos
            */
            $warehouseTransactionAcknowledgmentGeneric = WarehouseTransactionAcknowledgment::find($request->warehouse_transaction_acknowledgment_id);
            if(!$warehouseTransactionAcknowledgmentGeneric) {
                return response()->json([
                    'message' => 'Error, Warehouse Transaction Acknowledgment not found'
                ], 404);
            }
            $warehouseTransactionAcknowledgmentType = InventoryTransactionType::find($warehouseTransactionAcknowledgmentGeneric->inventory_transaction_type_id);

            $request->merge([
                'pos_product_warehouse_id' => $warehouseTransactionAcknowledgmentGeneric->pos_product_warehouse_id,
            ]);

            if($request->pos_product_warehouse_id){
                $posProductWarehouse = PosProductWarehouse::find($request->pos_product_warehouse_id);
                if (!$posProductWarehouse) {
                    return response()->json([
                        'message' => 'Error, pos product warehouse not found',
                        'data' => $request->pos_product_warehouse_id
                    ], 404);
                }
            }
            
            /* 
            * Encontramos los tipos de transacciones de inventario
            */
            $inventoryTransactionType1 = InventoryTransactionType::where('name', 'compra_de_stock')->first();
            $inventoryTransactionType2 = InventoryTransactionType::where('name', 'devolucion_de_stock')->first();
            $inventoryTransactionType3 = InventoryTransactionType::where('name', 'actualizacion_de_propiedades')->first();
            $inventoryTransactionType4 = InventoryTransactionType::where('name', 'transferencia_de_stock_a_tienda')->first();
            $inventoryTransactionType5 = InventoryTransactionType::where('name', 'devolucion_de_stock_a_almacen')->first();

            if($inventoryTransactionType1->id == $warehouseTransactionAcknowledgmentType->id) {
                
                $stadiumLocation = StadiumLocation::find($request->stadium_location_id);
                if(!$stadiumLocation) {
                    return response()->json([
                        'message' => 'Error, Stadium Location not found'
                    ], 404);
                }
                /* 
                * Comprobar que el acuse esta activo y tenga 'warehouse_supplier_id' 
                */
                $warehouseTransactionAcknowledgment = WarehouseTransactionAcknowledgment::where('id', $request->warehouse_transaction_acknowledgment_id)
                    ->where('is_active', 1)
                    ->whereNotNull('warehouse_supplier_id')
                    ->first();
                if(!$warehouseTransactionAcknowledgment) {
                    return response()->json([
                        'message' => 'Error, Warehouse Transaction Acknowledgment not found or not active or not have Warehouse Supplier'
                    ], 404);
                }

                /* 
                * Comprobar que el pos_product_warehouse y estadio no tenga un inventario global para el catalogo de productos de almacén
                */
                foreach($request->product_data as $product) {
                    $globalInventory = GlobalInventory::where('warehouse_product_catalog_id', $product['warehouse_product_catalog_id'])
                        ->where('stadium_location_id', $request->stadium_location_id)
                        ->where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
                        ->where('clothing_size_id', $product['clothing_size_id'] ?? null)
                        ->first();

                    if($globalInventory) {
                        /* 
                        * Actualizar el stock en el inventario global
                        */
                        $globalInventory->current_stock = $globalInventory->current_stock + $product['stock_received'];
                        $globalInventory->purchase_price = $product['purchase_price'] ?? $globalInventory->purchase_price;
                        $globalInventory->sale_price = $product['sale_price'] ?? $globalInventory->sale_price;
                        $globalInventory->discount_sale_price = $product['discount_sale_price'] ?? $globalInventory->discount_sale_price;
                        $globalInventory->save();

                         /* 
                        * Creacion de una nueva instancia de Global Inventory Transaction
                        */
                        $globalInventoryTransaction = new GlobalInventoryTransaction();
                        $globalInventoryTransaction->global_inventory_id = $globalInventory->id;
                        $globalInventoryTransaction->inventory_transaction_type_id = $warehouseTransactionAcknowledgmentType->id;
                        $globalInventoryTransaction->warehouse_transaction_acknowledgment_id = $request->warehouse_transaction_acknowledgment_id;
                        $globalInventoryTransaction->previous_stock = $globalInventory->current_stock - $product['stock_received'];
                        $globalInventoryTransaction->stock_movement = $product['stock_received'];
                        $globalInventoryTransaction->new_stock = $globalInventory->current_stock;
                        $globalInventoryTransaction->previous_sale_price = $globalInventory->sale_price;
                        $globalInventoryTransaction->new_sale_price = $globalInventory->sale_price;
                        $globalInventoryTransaction->previous_discount_price = $globalInventory->discount_sale_price;
                        $globalInventoryTransaction->new_discount_price = $globalInventory->discount_sale_price;
                        $globalInventoryTransaction->reason = $request->reason ?? 'Compra de Stock';
                        $globalInventoryTransaction->save();

                    } else {
                         /* 
                        * Creacion de una nueva instancia de Global Inventory
                        */
                        $globalInventory = new GlobalInventory();
                        $globalInventory->warehouse_product_catalog_id = $product['warehouse_product_catalog_id'];
                        $globalInventory->stadium_location_id = $request->stadium_location_id;
                        $globalInventory->pos_product_warehouse_id = $request->pos_product_warehouse_id;
                        $globalInventory->clothing_size_id = $product['clothing_size_id'] ?? null;
                        $globalInventory->current_stock = $product['stock_received'];
                        $globalInventory->purchase_price = $product['purchase_price'];
                        $globalInventory->sale_price = $product['sale_price'] ?? 0.00;
                        $globalInventory->discount_sale_price = $product['discount_sale_price'] ?? $product['sale_price'] ?? 0.00;
                        $globalInventory->save();

                         /* 
                        * Creacion de una nueva instancia de Global Inventory Transaction
                        */
                        $globalInventoryTransaction = new GlobalInventoryTransaction();
                        $globalInventoryTransaction->global_inventory_id = $globalInventory->id;
                        $globalInventoryTransaction->inventory_transaction_type_id = $warehouseTransactionAcknowledgmentType->id;
                        $globalInventoryTransaction->warehouse_transaction_acknowledgment_id = $request->warehouse_transaction_acknowledgment_id;
                        $globalInventoryTransaction->previous_stock = 0;
                        $globalInventoryTransaction->stock_movement = $product['stock_received'];
                        $globalInventoryTransaction->new_stock = $product['stock_received'];
                        $globalInventoryTransaction->previous_sale_price = 0;
                        $globalInventoryTransaction->new_sale_price = $product['sale_price'] ?? 0.00;
                        $globalInventoryTransaction->previous_discount_price = 0;
                        $globalInventoryTransaction->new_discount_price = $product['discount_sale_price'] ?? $product['sale_price'] ?? 0.00;
                        $globalInventoryTransaction->reason = $request->reason ?? 'Compra de Stock';
                        $globalInventoryTransaction->save();
                    }

                }

                DB::commit();

                return response()->json([
                    'message' => 'success, global Inventories created for this Warehouse Product Catalog and Stadium Location',
                ], 201);

            } else if($inventoryTransactionType4->id == $warehouseTransactionAcknowledgmentType->id) {
                /* 
                * Comprobar que el acuse esta activo y no tenga 'warehouse_supplier_id' 
                */
                $warehouseTransactionAcknowledgment = WarehouseTransactionAcknowledgment::where('id', $request->warehouse_transaction_acknowledgment_id)
                    ->where('is_active', 1)
                    ->whereNull('warehouse_supplier_id')
                    ->first();
                if(!$warehouseTransactionAcknowledgment) {
                    return response()->json([
                        'message' => 'Error, Warehouse Transaction Acknowledgment not found or not active or have Warehouse Supplier'
                    ], 404);
                }

                /* 
                * recibimos un array de 'global_inventories' y creamos instacias de warehouse_product_inventories para cada uno
                */
                foreach($request->global_inventories as $global_inventory) {
                    $globalInventory = GlobalInventory::find($global_inventory['global_inventory_id']);
                    if(!$globalInventory) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Error, Global Inventory not found',
                            'data' => $global_inventory['global_inventory_id']
                        ], 404);
                    }

                    /* 
                    * Comprobar que el 'global inventory' pertenesca al mismo pos product warehouse que se esta transfiriendo
                    */
                    if($globalInventory->pos_product_warehouse_id != $request->pos_product_warehouse_id) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Error, Global Inventory not belong to this POS Product Warehouse'
                        ], 404);
                    }

                    /* 
                    * Comprobar que haya suficiente stock en el inventario global
                    */
                    if($globalInventory->current_stock < $global_inventory['stock_transfered']) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Error, not enough stock in Global Inventory'
                        ], 404);
                    }

                    /* 
                    * comprobar si el warehouse_product_inventory ya existe
                    */
                    $warehouseProductInventory = WarehouseProductInventory::where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
                        ->where('warehouse_product_catalog_id', $globalInventory->warehouse_product_catalog_id)
                        ->where('global_inventory_id', $global_inventory['global_inventory_id'])
                        ->first();
                    
                    if($warehouseProductInventory) {
                        /* 
                        * Actualizar el stock en el inventario global
                        */
                        $warehouseProductInventory->stock = $warehouseProductInventory->stock + $global_inventory['stock_transfered'];
                        $warehouseProductInventory->save();
                    } else {
                        /* 
                        * Creacion de una nueva instancia de Warehouse Product Inventory
                        */
                        $warehouseProductInventory = new WarehouseProductInventory();
                        $warehouseProductInventory->pos_product_warehouse_id = $request->pos_product_warehouse_id;
                        $warehouseProductInventory->warehouse_product_catalog_id = $globalInventory->warehouse_product_catalog_id;
                        $warehouseProductInventory->global_inventory_id = $global_inventory['global_inventory_id'];
                        $warehouseProductInventory->sale_price = $globalInventory->sale_price;
                        $warehouseProductInventory->discount_sale_price = $globalInventory->discount_sale_price;
                        $warehouseProductInventory->stock = $global_inventory['stock_transfered'];
                        $warehouseProductInventory->is_active = true;
                        $warehouseProductInventory->save();
                    }

                    /*
                    * Creacion de una nueva instancia de Global Inventory Transaction
                    */
                    $globalInventoryTransaction = new GlobalInventoryTransaction();
                    $globalInventoryTransaction->global_inventory_id = $globalInventory->id;
                    $globalInventoryTransaction->inventory_transaction_type_id = $warehouseTransactionAcknowledgmentType->id;
                    $globalInventoryTransaction->warehouse_transaction_acknowledgment_id = $request->warehouse_transaction_acknowledgment_id;
                    $globalInventoryTransaction->previous_stock = $globalInventory->current_stock;
                    $globalInventoryTransaction->stock_movement = $global_inventory['stock_transfered'];
                    $globalInventoryTransaction->new_stock = $globalInventory->current_stock - $global_inventory['stock_transfered'];
                    $globalInventoryTransaction->previous_sale_price = $globalInventory->sale_price;
                    $globalInventoryTransaction->new_sale_price = $globalInventory->sale_price;
                    $globalInventoryTransaction->previous_discount_price = $globalInventory->discount_sale_price;
                    $globalInventoryTransaction->new_discount_price = $globalInventory->discount_sale_price;
                    $globalInventoryTransaction->reason = $request->reason ?? 'Transferencia a Tienda';
                    $globalInventoryTransaction->save();

                    /* 
                    * Actualizar el stock en el inventario global
                    */
                    $globalInventory->current_stock = $globalInventory->current_stock - $global_inventory['stock_transfered'];
                    $globalInventory->save();

                }

                DB::commit();

                return response()->json([
                    'message' => 'Success, Stock Transfered to Store',
                ], 201);
            } else if($inventoryTransactionType3->id == $warehouseTransactionAcknowledgmentType->id) {

                /* 
                * Comprobar que el acuse esta activo y no tenga 'warehouse_supplier_id'
                */
                $warehouseTransactionAcknowledgment = WarehouseTransactionAcknowledgment::where('id', $request->warehouse_transaction_acknowledgment_id)
                    ->where('is_active', 1)
                    ->whereNull('warehouse_supplier_id')
                    ->first();
                if(!$warehouseTransactionAcknowledgment) {
                    return response()->json([
                        'message' => 'Error, Warehouse Transaction Acknowledgment not found or not active or have Warehouse Supplier'
                    ], 404);
                }

                $globalInventory = GlobalInventory::find($request->global_inventory_id);
                if(!$globalInventory) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Error, Global Inventory not found',
                        'data' => $request->global_inventory_id
                    ], 404);
                }

                /* 
                * Creacion de una nueva instancia de Global Inventory Transaction
                */
                $globalInventoryTransaction = new GlobalInventoryTransaction();
                $globalInventoryTransaction->global_inventory_id = $globalInventory->id;
                $globalInventoryTransaction->inventory_transaction_type_id = $warehouseTransactionAcknowledgmentType->id;
                $globalInventoryTransaction->warehouse_transaction_acknowledgment_id = $request->warehouse_transaction_acknowledgment_id;
                $globalInventoryTransaction->previous_stock = $globalInventory->current_stock;
                $globalInventoryTransaction->stock_movement = 0;
                $globalInventoryTransaction->new_stock = $globalInventory->current_stock;
                $globalInventoryTransaction->previous_sale_price = $globalInventory->sale_price;
                $globalInventoryTransaction->new_sale_price = $request->sale_price ?? $globalInventory->sale_price;
                $globalInventoryTransaction->previous_discount_price = $globalInventory->discount_sale_price;
                $globalInventoryTransaction->new_discount_price = $request->discount_sale_price ?? $globalInventory->discount_sale_price;
                $globalInventoryTransaction->reason = $request->reason ?? 'Actualizacion de Propiedades';
                $globalInventoryTransaction->save();

                /* 
                * actualizamos propiedades de Global Inventory
                */
                $globalInventory->purchase_price = $request->purchase_price ?? $globalInventory->purchase_price; 
                $globalInventory->sale_price = $request->sale_price ?? $globalInventory->sale_price;
                $globalInventory->discount_sale_price = $request->discount_sale_price ?? $globalInventory->discount_sale_price;
                $globalInventory->save();

                /* 
                * finalizar acuse
                */
                $warehouseTransactionAcknowledgment->is_active = 0;
                $warehouseTransactionAcknowledgment->save();

                DB::commit();

                return response()->json([
                    'message' => 'Success, Global Inventory Properties Updated',
                    'data' => $globalInventory
                ], 201);


            }


        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, cannot create Global Inventory',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
