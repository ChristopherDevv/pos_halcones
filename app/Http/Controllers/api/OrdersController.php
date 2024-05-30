<?php

namespace App\Http\Controllers\api;

use App\Mail\Compra;
use App\Mail\CompraMembresia;
use App\Mail\PedidoEntregadoMail;
use App\Mail\PedidoEnviadoMail;
use App\Mail\PedidoPorEntregarMail;
use App\Mail\SendMail;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use App\Models\Interfaces\EstatusOrdenesEnum;
use App\Models\Orders;
use App\Models\OrdersProductos;
use App\Models\Productos;
use App\Models\ProductosTallas;
use App\Models\Tallas;
use App\Models\User;
use App\Models\OrdersMembresias;
use App\Models\UsuarioMembresia;
use App\Models\Descuentos;
use App\Models\DescuentoProducto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Util\Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class OrdersController extends Controller
{
    public function index(Request $request) {
        try {
            $orders = array();
           if($request->has('config')) {
               $config = json_decode($request->get('config'));
               $orders = $this->resolveFilter($config);
           }else {
               if($request->has('idUser')) {
                   $usersId = $request->get('idUser');
                   $orders = Orders::where([
                       ['status','>=',EstatusOrdenesEnum::PAYED],
                       ['users_id',$usersId]
                   ]);
               }else {
                   $orders = Orders::where('status','>=',EstatusOrdenesEnum::PAYED);
               }
           }
            $orders = $orders->with(['user','sucursal','productos','direccion.estado','direccion.municipio','direccion.ciudad','atendedBy','paqueteria', 'membresia.membresia.imagenes'])->get()->sortBy('status');
            foreach ($orders as $order){
                $aux = $order->productos;
                unset($order->productos);
                $order->productos = collect($aux)->unique(
                    function ($productUnique) {
                        return $productUnique['talla'].$productUnique['producto_comprado'];
                    }
                )->map(
                    function ($product) use ($aux){
                        $product->cant_total =  collect($aux)->where('id','=',$product->id)->where('talla',$product->talla)->count();
                        $product->total_comprado =  $product->price * $product->cant_total;
                        return $product;
                    }
                )->values();
            }
            return $this->buildOrdersForStatus($orders);
        }catch (\Exception $e) {
            $response =  new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }

    public function buildOrdersForStatus($orders){
        return $orders->groupBy('status')->map(
            function ($order,$keyStatus) {
                return [
                  'status' => $keyStatus,
                  'orders' => $order
                ];
            }
        )->values();
    }

    public function page(Request $request) {
        try {

            $config = json_decode($request->get('config'));

            $orders = $this->resolveFilter($config);

            $orders = $orders->with([ 'direccion.estado','direccion.municipio','sucursal','direccion.ciudad','productos','user','atendedBy','paqueteria','membresia.membresia.imagenes' ]);

            $orders = $orders->select( '*', DB::Raw("date(creation_date) as fecha_compra"));

            $resultSet =  $orders->paginate( $request->get('pagination') ? $request->get('pagination') : 20 );

            return $resultSet;

        }catch (\Exception $e) {

            $response =  new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_PAGE()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_PAGE()->getCode(),$e->getMessage());

            return response()->json($response,505);

        }
    }

    public function show($id, Orders $orders){
        try{

            $numberControlMembership = "";

            $order = $orders->where('id',$id)->with(['productos','sucursal','direccion.estado','direccion.municipio','direccion.ciudad','paqueteria','user.usuarioMembresias', 'atendedBy' => function($atendedBy){

                $atendedBy->select('id', 'nombre', 'apellidoP', 'apellidoM','correo');

            }, 'membresia.membresia.imagenes'])->firstOrFail();

            // Zuriel DA
            // Se agregan los descuentos si se aplican para ese producto

            Arr::first($order->productos, function ($product, $key) use (&$order)
            {
                $dicounts = OrdersProductos::select('id')->with(['descuentos' => function($descuentos)
                {
                    $descuentos->with(['descuento'=>function($descuento){

                        $descuento-> select('id', 'discount');

                    }]);

                }])->findOrFail($product->pivot->id);

                data_fill($order->productos[$key], 'descuentos', $dicounts);
            });

            // Zuriel DA
            // Se agrega el tipo de pago que se ha realizado.

            switch ($order-> type_payment) {
                case 1:
                    data_set($order, "name_type_payment", "Efectivo");
                    break;
                case 2:
                    data_set($order, "name_type_payment", "Tarjeta");
                    break;
                case 3:
                    data_set($order, "name_type_payment", "Cortesia");
                    break;
            }

            $aux = $order->productos;
            unset($order->productos);
            $order->productos = collect($aux)->unique(
                function ($productUnique) {
                    return $productUnique['talla'].$productUnique['producto_comprado'];
                }
            )->map(
                function ($product) use ($aux){
                    $product->cant_total =  collect($aux)->where('id','=',$product->id)->where('talla',$product->talla)->count();
                    $product->total_comprado =  $product->price * $product->cant_total;
                    return $product;
                }
            )->values();
            return $order;
        }catch (\Exception $e){

        }
    }

    public function store(Request $request, Orders $orders) {
        try{

            $productRequest = $request->only(
                ['total','cant_total','users_id','atended_by','directions_id','isPersonaOtro','personaOtro','type_payment','status','type_origin','paqueterias_id','sucursales_id', 'is_reserved_for_pick']
            );

            if($request->has('cant')){
                $productRequest['cant_total'] = $request-> get('cant');
            }

            //  ZurielDA
                if($request->has('motiveCoutersy')){
                    $productRequest['motiveCoutersy'] = $request-> get('motiveCoutersy');
                }
            //

            DB::beginTransaction();

            $resultSet = $orders->create($productRequest);

            if($request->has('productos')){
                $this->prepareProducts($resultSet->id,$request->get('productos'));
            }

            if($request->has('detalles') && $request->get('status') === 6) {
                $this->prepareProduct($resultSet->id, $request->get('detalles'), $request->get('cant'), $request->get('talla'));
                $this->updateStockOFProducts($resultSet->id);
            }

            DB::commit();

            $response =  new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$this->show($resultSet->id,$orders));
            return response()->json($response);

        }catch (\Exception $e){
            DB::rollBack();
            $response =  new DataResponse('No se pudo completar su compra. ',ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }




/**
 *  ZurielDA
 */
    public function storeMembership(Request $request, Orders $orders) {

        try{

            $user = User::select('id')->withCount('usuarioMembresias')->find($request-> get('users_id'));

            if ($user->usuario_membresias_count) {


                $response =  new DataResponse('Ya cuenta con una membresia.',ErroresExceptionEnum::OBJECT_FOUND()->getCode(), true);
                return response()->json($response,422);

            }

            $productRequest = $request->only(
                ['total','cant_total','users_id','atended_by','directions_id','isPersonaOtro','personaOtro','type_payment','status','type_origin','paqueterias_id','sucursales_id', 'is_reserved_for_pick']
            );

            $resultSet = $orders->create($productRequest);

            $request->merge(['idOrders' => $resultSet-> id]);

            OrdersMembresias::create($request->only('idOrders','idMembresia', 'price', 'benefit'));


            $userMembership = new UsuarioMembresia;

            $userMembership-> idUser = $request-> get('users_id');
            $userMembership-> idMemberShip = $request-> get('idMembresia');
            $userMembership-> finished_at = Carbon::now()-> addYears(1)->month(1)->day(1)->hour(1);
            $userMembership-> numberControl = Str::replace(' ','',Str::replace(':','',Str::replace('-','', Carbon::now()-> addYears(1)->month(1)->day(1)->hour(1)->toDateTimeString() )));


            $userMembership-> save();

            $response =  new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$this->show($resultSet->id,$orders));
            return response()->json($response);

        }catch (\Exception $e){
            DB::rollBack();
            $response =  new DataResponse('No se pudo completar su compra. ',ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }
/**
 *
 */

    private function  prepareProducts($idOrder,$products) {

        // $ordersProducts = array();
        // foreach ($products as $product){
        //     for($index = 0;$index < $product['cant'];$index ++){

        //         $productosTallas = ProductosTallas::where([
        //             ['productos_id','=',$product['id']],
        //             ['tallas_id','=',$product['talla']]
        //         ])->first();

        //         if($product['cant'] > $productosTallas->cant) {

        //             $talla = collect($product['detalle']['tallas'])->where(
        //                 'id','=',$productosTallas->tallas_id
        //             )->first();
        //             throw new \Exception('No hay artículos suficientes para este producto: '.$product['detalle']['title'].' Talla: '.$talla['title']);
        //         }

        //         array_push($ordersProducts,[
        //             'orders_id' => $idOrder,
        //             'productos_id' => $product['id'],
        //             'tallas_id' => $product['talla']
        //         ]);
        //     }
        // }
        // OrdersProductos::insert($ordersProducts);

        // Zuriel DA
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $generalDiscount = app(\App\Http\Controllers\api\DescuentosController::class)->get_discount_general_all_people();

        $idGeneralDiscount = 0;

        if (count($generalDiscount))
        {
            $idGeneralDiscount = $generalDiscount[0]-> id;
        }

        foreach ($products as $product){

            for($index = 0;$index < $product['cant']; $index ++){

                $productosTallas = ProductosTallas::where([
                    ['productos_id','=',$product['id']],
                    ['tallas_id','=',$product['talla']]
                ])->first();

                if($product['cant'] > $productosTallas->cant) {

                    $talla = collect($product['detalle']['tallas'])->where(
                        'id','=',$productosTallas->tallas_id
                    )->first();
                    throw new \Exception('No hay artículos suficientes para este producto: '.$product['detalle']['title'].' Talla: '.$talla['title']);
                }

                $ordersProductos = new OrdersProductos;

                $ordersProductos-> orders_id = $idOrder;
                $ordersProductos-> productos_id = $product['id'];
                $ordersProductos-> tallas_id = $product['talla'];
                $ordersProductos-> priceProduct  = $product['detalle']['priceWithoutDiscount'];
                $ordersProductos-> discountApplied  = $product['detalle']['discountApplied'];


                $ordersProductos->save();

                $idDiscounts = collect([]);

                if ($idGeneralDiscount)
                {
                    $idDiscounts->push( $idGeneralDiscount );
                }

                if ($product['detalle']['discount'])
                {
                    $idDiscounts->push( $product['detalle']['discount']['id'] );
                }

                Arr::first($product['detalle']['categorias']['descuento_sub_categoria'], function ($descuento, $key) use (&$idDiscounts)
                {
                    $idDiscounts->push( $descuento['id'] );
                });

                Arr::first($product['detalle']['categorias']['padre']['descuento_categorias'], function ($descuento, $key) use (&$idDiscounts)
                {
                    $idDiscounts->push( $descuento['id'] );
                });

                $idDiscounts-> each(function ($descuento, $key) use ($ordersProductos, &$idDiscountsSave)
                {
                    $descuentoProducto = new DescuentoProducto;

                    $descuentoProducto-> idOrderProduct = $ordersProductos-> id;
                    $descuentoProducto-> idDiscount = $descuento;

                    $descuentoProducto->save();
                });
            }

        }
    }


    private function  prepareProduct($idOrder,$product,$cant,$talla) {
        // $ordersProducts = array();
        //     for($index = 0;$index < $cant;$index ++){
        //         array_push($ordersProducts,['orders_id' => $idOrder, 'productos_id' => $product['id'], 'tallas_id' => $talla]);
        //     }
        // OrdersProductos::insert($ordersProducts);

        // Zuriel DA
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $generalDiscount = Descuentos::where([
            ['idCategory', '=', null ],
            ['idSubCategory', '=', null],
            ['idProduct', '=', null],
            ['status', '=', 'Activo']
        ])->get();

        $idGeneralDiscount = 0;

         // Descuento general
         if (count($generalDiscount))
         {
            $idGeneralDiscount = $generalDiscount[0]->id;
         }

        for($index = 0;$index < $cant; $index ++)
        {
            $ordersProductos = new OrdersProductos;

            $ordersProductos-> orders_id = $idOrder;
            $ordersProductos-> productos_id = $product['id'];
            $ordersProductos-> tallas_id = $talla;
            $ordersProductos-> priceProduct  = $product['priceWithoutDiscount'];
            $ordersProductos-> priceProduct  = $product['priceWithoutDiscount'];
            $ordersProductos-> discountApplied  = $product['discountApplied'];

            $ordersProductos->save();

            $idDiscounts = collect([]);

            if ($idGeneralDiscount)
            {
                $idDiscounts->push( $idGeneralDiscount );
            }

            if ($product['discount'])
            {
                $idDiscounts->push( $product['discount']['id'] );
            }

            Arr::first($product['categorias']['descuento_sub_categoria'], function ($descuento, $key) use (&$idDiscounts)
            {
                $idDiscounts->push( $descuento['id'] );
            });

            Arr::first($product['categorias']['padre']['descuento_categorias'], function ($descuento, $key) use (&$idDiscounts)
            {
                $idDiscounts->push( $descuento['id'] );
            });

            $idDiscounts-> each(function ($descuento, $key) use ($ordersProductos, &$idDiscountsSave)
            {
                $descuentoProducto = new DescuentoProducto;

                $descuentoProducto-> idOrderProduct = $ordersProductos-> id;
                $descuentoProducto-> idDiscount = $descuento;

                $descuentoProducto->save();
            });
        }

    }

    public function delete($id,Orders $orders) {
        try {
            $order =  $orders->where('id',$id);
            $order->update([
               'status' => 0
            ]);
            $response =  new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(),$id    );
            return response()->json($response);
        }catch (\Exception $e) {
            $response =  new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }

    }

    public function edit($id,Request $request,Orders $orders) {

        try {
            $dataUpdate = $request->all();
            DB::beginTransaction();
            $order = $orders->where('id',$id);
            $result = $order->update($dataUpdate);
            if($request->has('status') && $request->get('status') == 3) {

                //ZurielDA
                // Se envia correo de que se ha comprado una membresia
                $orderWithMembership =  Orders::with('membresia.membresia.imagenes', 'sucursal')->find($id);
                if( $orderWithMembership-> membresia )
                {
                    Mail::to( User::where('id',$orderWithMembership->users_id)->first()->correo )->send(new CompraMembresia($orderWithMembership));
                    //
                }
                else
                {
                    $this->updateStockOFProducts($id);
                    $order = $order->first();
                    $this->sendMai($order->users_id, $order->id, $order);
                }
            }
            if($request->has('status') && $request->get('status') === 5) {

                $orderPaquteria = $order->with(['paqueteria','user'])->first();
                if(!$orderPaquteria->is_reserved_for_pick){
                    $this->enviarCorreoDeEnviado($orderPaquteria->user->correo,$orderPaquteria->paqueteria,$orderPaquteria->num_seguimiento);
                }

            }
            if($request->has('status') && $request->get('status') === 6) {

                //ZurielDA
                // Se envia correo de que se ha entregado la mebresia
                $orderWithMembership =  Orders::with('membresia.membresia.imagenes', 'sucursal')->find($id);
                if( $orderWithMembership-> membresia )
                {
                    Mail::to( User::where('id',$orderWithMembership->users_id)->first()->correo )->send(new CompraMembresia($orderWithMembership));
                    //
                }
                else
                {
                    $orderSucursal = $order->with(['sucursal','user'])->first();
                    if($orderSucursal->is_reserved_for_pick) {
                        $this->enviarCorreoDeEntregado($orderSucursal->user->correo,$orderSucursal->sucursal,$orderSucursal->num_control);
                    }
                }
            }

            if($request->has('status') && $request->get('status') === 5) {

                //ZurielDA
                // Se envia correo de que la membresia esta lista para ser entregada
                $orderWithMembership =  Orders::with('membresia.membresia.imagenes', 'sucursal')->find($id);
                if( $orderWithMembership-> membresia )
                {
                    Mail::to( User::where('id',$orderWithMembership->users_id)->first()->correo )->send(new CompraMembresia($orderWithMembership));
                    //
                }
                else
                {
                    $orderSucursal = $order->with(['sucursal','user'])->first();
                    if($orderSucursal->is_reserved_for_pick) {
                        $this->enviarCorreoDePorEntregar($orderSucursal->user->correo,$orderSucursal->sucursal,$orderSucursal->num_control);
                    }
                }
            }
            if(!$result) {
                throw  new \Exception('Ocurrio un error en lo datos para actualizar');
            }
            DB::commit();
            $response =  new DataResponse('Se ha actualizado el pedido',ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(),$order);
            return response()->json($response);
        }catch (\Exception $e) {
            DB::rollBack();
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),$e->getTrace());
            return response()->json($response,505);
        }
    }



    public function resolveFilter($config, $ordersQuery = NULL) {
        $orders = Orders::from('orders as o')->where('status','>',0)->select(
            '*',
            DB::raw('date(updated_date) as fecha_actualizacion'),
            DB::raw('date(creation_date) as fecha_creacion')
        )->orderBy('creation_date', 'desc') ;
        $resultSet = NULL;
        switch ($config->type) {
            case 'where':
                $resultSet = $orders->where($config->key,$config->operator,$config->value);
                break;
            case 'manyWhere':
                $resultSet = $orders->where($config->wheres);
                break;
            case 'between':
                $resultSet = $orders->whereBetween($config->key,$config->value);
                break;
            case 'whereIn':
                $resultSet = $orders->whereIn($config->key,$config->value);
                break;
            case 'having':
                $resultSet = $orders->having($config->key,$config->operator,$config->value);
                break;
            default:
                $resultSet = $orders;
                break;
        }
        return $resultSet;
    }

    private function enviarCorreoDeEnviado($correo,$paqueteria,$folio) {
        Mail::to($correo)->send(new PedidoEnviadoMail($paqueteria,$folio));
    }

    private function enviarCorreoDeEntregado($correo,$sucursal,$folio) {
        Mail::to($correo)->send(new PedidoEntregadoMail($sucursal,$folio));
    }
    private function enviarCorreoDePorEntregar($correo,$sucursal,$folio) {
        Mail::to($correo)->send(new PedidoPorEntregarMail($sucursal,$folio));
    }

    public function updateStockOFProducts($idOrder) {
        try{
            $productos = OrdersProductos::from('orders_productos as op')->where('orders_id',intval($idOrder))->groupBy('tallas_id','productos_id')->select(
                'op.productos_id',
                'op.tallas_id as tallas',
                DB::raw('count(op.productos_id) as total_productos')
            );
            collect($productos->get())->map(
                function ($producto){
                       $p = Productos::where('id',$producto->productos_id);
                       $tallaProductos= ProductosTallas::where([
                           ['tallas_id','=',$producto->tallas],['productos_id','=',$producto->productos_id]
                       ]);
                       $auxTallaProducto = $tallaProductos->firstOrFail();
                       $aux = $p->first();
                       $tallaProductos = $tallaProductos->update([
                           'cant' => $auxTallaProducto->cant - $producto->total_productos
                       ]);

                       Log::info("Total de productos".json_encode($producto));
                       Log::info("Producto".json_encode($aux));
                       $r = $aux->stock - $producto->total_productos;
                       $resultSet = $p->update(
                            [
                                'stock' => $r
                            ]
                       );
                       if(!$resultSet || !$tallaProductos) {
                            throw new \Exception('Error al actualizar la cantidad de productos en el stock');
                       }
                }
            );
        }catch (\Exception $e){
            throw new \Exception('Error al actualizar los productos del stock '.$e->getMessage());
        }
    }

    public function corteDeCaja($idUser) {
        try {
            $ordes = Orders::from('orders as o')->where(
                [
                    ['users_id','=',$idUser],
                    ['status','=',EstatusOrdenesEnum::COMPLETED]
                ]
            )->select(
                '*',
                DB::raw('date(o.updated_date) as fecha_actualizacion'),
                DB::raw(
                    "(CASE
                        WHEN o.type_payment = 1 THEN 'Efectivo'
                        WHEN o.type_payment = 2 and o.type_origin=1 THEN 'Paypal'
                        WHEN o.type_payment = 2 and o.type_origin=2 THEN 'Tarjeta'
                        WHEN o.type_payment = 3 THEN 'Cortesia'
                        END) as tipo_compra
                    "
                ),
            )->with(
                'atendedBy',
                'direccion',
                'productos'
            )->get();


            $ordes = collect($ordes)->sortByDesc('fecha_actualizacion')->groupBy('fecha_actualizacion')->map(
                function ($ordersFechas, $key){
                    return [
                        'fecha' => $key,
                        'pedidos' => collect($ordersFechas),
                        'totalVendido' => collect($ordersFechas)->sum('total'),
                        'totalProducto' => collect($ordersFechas)->sum('cant_total')
                    ];
                }
            )->values();
            $hoy = Carbon::now()->isoFormat('Y-M-DD');
            $dataResponse= [
                'historial' => $ordes,
                'dia' => collect($ordes)->where('fecha','=',$hoy)
            ];
            return response()->json($dataResponse);
        }catch (\Exception $e){
            return  $e->getMessage();
        }
    }

    // ZurielDA

    public function corteDeCajaUsuario($idUser)
    {
        try {


            /**
             * Se obtiene las ordenes de el dia actaul
             */

            $dataNow = Carbon::now()->isoFormat('Y-M-DD');

            $toDayCurrentOrders = [
                'date'=> Carbon::now()->isoFormat('DD-MM-Y'),
                'totalOrdersCashPayment'=> 0,
                'totalOrdersCardPayment'=> 0,
                'totalOrdersCourtesyPayment'=> 0,
                'totalOrdersUnknownPayment'=> 0,
                'totalOrders'=> 0,

                'totalItemsOrderCashPayment'=> 0,
                'totalItemsOrderCardPayment'=> 0,
                'totalItemsOrderCourtesyPayment'=> 0,
                'totalItemsOrderUnknownPayment'=> 0,
                'totalItemsOrder'=> 0,

                'totalCostCashPayment'=> 0,
                'totalCostCardPayment'=> 0,
                'totalCostCourtesyPayment'=> 0,
                'totalCostOrderUnknownPayment'=> 0,
                'totalCost'=> 0,

                'itemsSold'=> collect([])
            ];

            User::select('id')-> with(['orders'=>function($orders) use ($dataNow)
            {
                $orders->select(['id', 'total', 'cant_total', 'status', 'atended_by', 'creation_date', 'num_control','type_payment', 'type_origin', 'paqueterias_id', 'sucursales_id'])
                    ->with('productos')->whereDate('creation_date', '=',$dataNow)-> where('status','=', EstatusOrdenesEnum::COMPLETED);

            }])-> where('id','=', $idUser)-> get()-> reject(function ($ordersToDay) use (&$toDayCurrentOrders)
            {
                $ordersToDay->orders->each(function ($order) use (&$toDayCurrentOrders)
                {
                    switch ($order->type_payment)
                    {
                        case 1:
                                $toDayCurrentOrders['totalOrdersCashPayment'] += 1;
                                $toDayCurrentOrders['totalItemsOrderCashPayment'] += $order->cant_total;
                                $toDayCurrentOrders['totalCostCashPayment'] += $order->total;
                            break;

                        case 2:
                                $toDayCurrentOrders['totalOrdersCardPayment'] += 1;
                                $toDayCurrentOrders['totalItemsOrderCardPayment'] += $order->cant_total;
                                $toDayCurrentOrders['totalCostCardPayment'] += $order->total;
                            break;

                        case 3:
                                $toDayCurrentOrders['totalOrdersCourtesyPayment'] += 1;
                                $toDayCurrentOrders['totalItemsOrderCourtesyPayment'] += $order->cant_total;
                                $toDayCurrentOrders['totalCostCourtesyPayment'] += $order->total;
                            break;

                        default:
                                $toDayCurrentOrders['totalOrdersUnknownPayment'] += 1;
                                $toDayCurrentOrders['totalItemsOrderUnknownPayment'] += $order->cant_total;
                                $toDayCurrentOrders['totalCostOrderUnknownPayment'] += $order->total;
                            break;
                    }

                    $toDayCurrentOrders['totalOrders'] += 1;
                    $toDayCurrentOrders['totalItemsOrder'] += $order->cant_total;
                    $toDayCurrentOrders['totalCost'] += $order->total;

                    $order->productos->each(function ($producto, $key) use (&$toDayCurrentOrders, $order){

                        $find =  $toDayCurrentOrders['itemsSold']->contains(function ($item, $key) use ($order, $producto) {
                             return $item['id'] == $producto->id && $item['titulo_talla'] == $producto->titulo_talla && $item['type_payment'] == $order->type_payment;
                        });

                        if ($find)
                        {
                            $toDayCurrentOrders['itemsSold'] = $toDayCurrentOrders['itemsSold']->map(function ($item, $key) use ($order, $producto)
                            {
                                if ($item['id'] == $producto->id && $item['titulo_talla'] == $producto->titulo_talla && $item['type_payment'] == $order->type_payment)
                                {
                                    $item['cantidad'] += 1;
                                }

                                return $item;
                            });
                        }
                        else
                        {
                            $name_type_payment = '';

                            switch ($order->type_payment)
                            {
                                case 1:
                                        $name_type_payment = 'pago en efectivo';
                                    break;

                                case 2:
                                        $name_type_payment = 'pago con tarjeta';
                                    break;

                                case 3:
                                        $name_type_payment = 'cortesia';
                                    break;

                                default:
                                        $name_type_payment = 'pago desconocido';
                                    break;
                            }

                            $toDayCurrentOrders['itemsSold']->push([
                                'id'=> $producto->id,
                                'title'=> $producto->title,
                                'price'=> $producto->price,
                                'type_payment'=> $order->type_payment,
                                'name_type_payment'=> $name_type_payment,
                                'titulo_talla'=> $producto->titulo_talla,
                                'images'=> $producto->images[0],
                                'cantidad'=> 1
                            ]);
                        }
                    });

                });

            });

            /**
             * Se obtienen todas las ordenas diferentes al dia actual.
             */

            $recordOrders = collect([]);

            User::select('id')-> with(['orders'=>function($orders) use($dataNow)
            {
                $orders->select(['id', 'total', 'cant_total', 'status', 'atended_by', 'creation_date', 'num_control','type_payment', 'type_origin', 'paqueterias_id', 'sucursales_id'])
                       ->with('productos')->whereDate('creation_date', '!=', $dataNow)-> where('status','=', EstatusOrdenesEnum::COMPLETED);
            }])-> where('id','=', $idUser)-> get()
            -> reject(function ($user) use (&$recordOrders)
            {
                $user->orders->each(function($order, $key) use (&$recordOrders){

                    $find =  $recordOrders->contains(function ($item, $key) use ($order)
                    {
                        return $item['date'] == $order->creation_date->isoFormat('DD-MM-Y');
                    });

                    if ($find) {

                        $recordOrders = $recordOrders->map(function ($itemRecordOrder, $key) use ($order) {

                            if ($itemRecordOrder['date'] == $order->creation_date->isoFormat('DD-MM-Y'))
                            {
                                switch ($order->type_payment)
                                {
                                    case 1:
                                            $itemRecordOrder['totalOrdersCashPayment'] += 1;
                                            $itemRecordOrder['totalItemsOrderCashPayment'] += $order->cant_total;
                                            $itemRecordOrder['totalCostCashPayment'] += $order->total;
                                        break;

                                    case 2:
                                            $itemRecordOrder['totalOrdersCardPayment'] += 1;
                                            $itemRecordOrder['totalItemsOrderCardPayment'] += $order->cant_total;
                                            $itemRecordOrder['totalCostCardPayment'] += $order->total;
                                        break;

                                    case 3:
                                            $itemRecordOrder['totalOrdersCourtesyPayment'] += 1;
                                            $itemRecordOrder['totalItemsOrderCourtesyPayment'] += $order->cant_total;
                                            $itemRecordOrder['totalCostCourtesyPayment'] += $order->total;
                                        break;

                                    default:
                                            $itemRecordOrder['totalOrdersUnknownPayment'] += 1;
                                            $itemRecordOrder['totalItemsOrderUnknownPayment'] += $order->cant_total;
                                            $itemRecordOrder['totalCostOrderUnknownPayment'] += $order->total;
                                        break;
                                }

                                $itemRecordOrder['totalOrders'] += 1;
                                $itemRecordOrder['totalItemsOrder'] += $order->cant_total;
                                $itemRecordOrder['totalCost'] += $order->total;

                                $order->productos->each(function ($producto, $key) use (&$itemRecordOrder, $order){

                                    $find =  $itemRecordOrder['itemsSold']->contains(function ($item, $key) use ($order, $producto) {
                                         return $item['id'] == $producto->id && $item['titulo_talla'] == $producto->titulo_talla && $item['type_payment'] == $order->type_payment;
                                    });

                                    if ($find)
                                    {
                                        $itemRecordOrder['itemsSold'] = $itemRecordOrder['itemsSold']->map(function ($item, $key) use ($order, $producto)
                                        {
                                            if ($item['id'] == $producto->id && $item['titulo_talla'] == $producto->titulo_talla && $item['type_payment'] == $order->type_payment)
                                            {
                                                $item['cantidad'] += 1;
                                            }

                                            return $item;
                                        });
                                    }
                                    else
                                    {
                                        $name_type_payment = '';

                                        switch ($order->type_payment)
                                        {
                                            case 1:
                                                    $name_type_payment = 'pago en efectivo';
                                                break;

                                            case 2:
                                                    $name_type_payment = 'pago con tarjeta';
                                                break;

                                            case 3:
                                                    $name_type_payment = 'cortesia';
                                                break;

                                            default:
                                                    $name_type_payment = 'pago desconocido';
                                                break;
                                        }

                                        $itemRecordOrder['itemsSold']->push([
                                            'id'=> $producto->id,
                                            'title'=> $producto->title,
                                            'price'=> $producto->price,
                                            'type_payment'=> $order->type_payment,
                                            'name_type_payment'=> $name_type_payment,
                                            'titulo_talla'=> $producto->titulo_talla,
                                            'images'=> $producto->images[0],
                                            'cantidad'=> 1
                                        ]);
                                    }
                                });
                            }

                            return $itemRecordOrder;
                        });

                    }
                    else
                    {
                        $currentOrder = [
                            'date'=> '',
                            'totalOrdersCashPayment'=> 0,
                            'totalOrdersCardPayment'=> 0,
                            'totalOrdersCourtesyPayment'=> 0,
                            'totalOrdersUnknownPayment'=> 0,
                            'totalOrders'=> 0,

                            'totalItemsOrderCashPayment'=> 0,
                            'totalItemsOrderCardPayment'=> 0,
                            'totalItemsOrderCourtesyPayment'=> 0,
                            'totalItemsOrderUnknownPayment'=> 0,
                            'totalItemsOrder'=> 0,

                            'totalCostCashPayment'=> 0,
                            'totalCostCardPayment'=> 0,
                            'totalCostCourtesyPayment'=> 0,
                            'totalCostOrderUnknownPayment'=> 0,
                            'totalCost'=> 0,

                            'itemsSold'=> collect([])
                        ];

                        $currentOrder['date'] = $order-> creation_date->isoFormat('DD-MM-Y');

                        switch ($order->type_payment)
                        {
                            case 1:
                                    $currentOrder['totalOrdersCashPayment'] += 1;
                                    $currentOrder['totalItemsOrderCashPayment'] += $order->cant_total;
                                    $currentOrder['totalCostCashPayment'] += $order->total;
                                break;

                            case 2:
                                    $currentOrder['totalOrdersCardPayment'] += 1;
                                    $currentOrder['totalItemsOrderCardPayment'] += $order->cant_total;
                                    $currentOrder['totalCostCardPayment'] += $order->total;
                                break;

                            case 3:
                                    $currentOrder['totalOrdersCourtesyPayment'] += 1;
                                    $currentOrder['totalItemsOrderCourtesyPayment'] += $order->cant_total;
                                    $currentOrder['totalCostCourtesyPayment'] += $order->total;
                                break;

                            default:
                                    $currentOrder['totalOrdersUnknownPayment'] += 1;
                                    $currentOrder['totalItemsOrderUnknownPayment'] += $order->cant_total;
                                    $currentOrder['totalCostOrderUnknownPayment'] += $order->total;
                                break;
                        }

                        $currentOrder['totalOrders'] += 1;
                        $currentOrder['totalItemsOrder'] += $order->cant_total;
                        $currentOrder['totalCost'] += $order->total;

                        $order->productos->each(function ($producto, $key) use (&$currentOrder, $order){

                            $find =  $currentOrder['itemsSold']->contains(function ($item, $key) use ($order, $producto) {
                                 return $item['id'] == $producto->id && $item['titulo_talla'] == $producto->titulo_talla && $item['type_payment'] == $order->type_payment;
                            });

                            if ($find)
                            {
                                $currentOrder['itemsSold'] = $currentOrder['itemsSold']->map(function ($item, $key) use ($order, $producto)
                                {
                                    if ($item['id'] == $producto->id && $item['titulo_talla'] == $producto->titulo_talla && $item['type_payment'] == $order->type_payment)
                                    {
                                        $item['cantidad'] += 1;
                                    }

                                    return $item;
                                });
                            }
                            else
                            {
                                $name_type_payment = '';

                                switch ($order->type_payment)
                                {
                                    case 1:
                                            $name_type_payment = 'pago en efectivo';
                                        break;

                                    case 2:
                                            $name_type_payment = 'pago con tarjeta';
                                        break;

                                    case 3:
                                            $name_type_payment = 'cortesia';
                                        break;

                                    default:
                                            $name_type_payment = 'pago desconocido';
                                        break;
                                }

                                $currentOrder['itemsSold']->push([
                                    'id'=> $producto->id,
                                    'title'=> $producto->title,
                                    'price'=> $producto->price,
                                    'type_payment'=> $order->type_payment,
                                    'name_type_payment'=> $name_type_payment,
                                    'titulo_talla'=> $producto->titulo_talla,
                                    'images'=> $producto->images[0],
                                    'cantidad'=> 1
                                ]);
                            }
                        });

                        $recordOrders->push($currentOrder);
                    }
                });
            });


            return response()->json([
                'status'=> true,
                'user'=> User::select(['id', 'nombre', 'correo', 'apellidoP', 'apellidoM'])->find($idUser),
                'today'=> $toDayCurrentOrders,
                'record'=> $recordOrders
            ]);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status'=> false,
                'today'=> [],
                'record'=> []
            ]);
        }
    }
    //

    public function  indicadores() {
        $orders  = Orders::from('orders as o')
                ->join('orders_productos as op','o.id','=','op.orders_id')
                ->join('productos as p','op.productos_id','=','p.id')
                ->join('tallas as t','op.tallas_id','=','t.id')
                ->join('paqueterias as paq','o.paqueterias_id','=','paq.id')
                ->where(
                'o.status','>=',3
               )->groupBy('fecha','op.orders_id','proveniente','tipo_pago','paqueteria','op.tallas_id','op.productos_id','talla','producto')->select(
                    DB::raw("(case
                            when o.type_origin = 1 then 'Mobile'
                            when o.type_origin = 2 then 'Sucursal'
                        end) as proveniente"),
                   DB::raw("(case
                     when o.type_payment = 1 then 'Efectivo'
                     when o.type_payment = 2 then 'Tarjeta'
                     when o.type_payment = 3 then 'Sucursal'
                     end) as tipo_pago"),
                   DB::raw("(case
                         when o.paqueterias_id = 1 then 'Halcones'
                         when o.paqueterias_id = 2 then 'DHL'
                         when o.paqueterias_id = 3 then 'FedEx'
                   end) as paqueteria"),
                   DB::raw("date(o.creation_date) as fecha"),
                   'p.title as producto',
                    't.title as talla',
                   DB::raw("count(op.productos_id) as cant_comprado"),
                   DB::raw("sum(o.total) as total_comprado"))->orderBy('fecha','ASC')->get();
        $resultSet = null;
        $hoy = Carbon::now()->isoFormat('Y-M-DD');
        $historial = collect($orders)->groupBy('fecha')->map(
            function ($fechas, $keyFecha){
                return [
                    'fecha' => $keyFecha,
                    'historial' => $fechas
                ];
            }
        )->values();
        $paqueterias  =  collect($orders)->groupBy('paqueteria')->map(
            function ($paqueteria, $keyPaqueteria){
                return [
                    'paqueteria' => $keyPaqueteria,
                    'cant' => collect($paqueteria)->count()
                ];
            }
        )->values();
        return [
            'historial' => $historial,
            'hoy' => [
               'historial' => collect($historial)->where('fecha','=',$hoy)->values(),
                'tipoPago' => collect($orders)->where('fecha','=',$hoy)->groupBy('tipo_pago')->map(
                    function ($tiposPagos, $key){
                        return [
                            'tipoPago'=>  $key,
                            'cant' => collect($tiposPagos)->sum('cant_comprado'),
                            'total_vendido' => collect($tiposPagos)->sum('total_comprado')
                        ];
                    }
                )->values(),
                'totalVendido' => collect($orders)->where('fecha','=',$hoy)->sum('total_comprado'),
                'cantTotalVendido' =>  collect($orders)->where('fecha','=',$hoy)->sum('cant_comprado')
            ],
            'paqueterias'=> $paqueterias,
            'tipoPago' => collect($orders)->groupBy('tipo_pago')->map(
                function ($tiposPagos, $key){
                    return [
                        'tipoPago'=>  $key,
                        'cant' => collect($tiposPagos)->sum('cant_comprado'),
                        'total_vendido' => collect($tiposPagos)->sum('total_comprado')
                    ];
                }
            )->values(),
            'totalVendido' => collect($orders)->sum('total_comprado'),
            'cantTotalVendido' =>  collect($orders)->sum('cant_comprado'),
            'productos' => collect($orders)->duplicates('producto')->take(10)->map(
                function ($item,$key){
                    return [
                      'cant' => $key,
                      'articulo' => $item
                    ];
                }
            )->values()
        ];
    }

    public function sendMai($userId,$orderId, $orders) {
        $correo = User::where('id',$userId)->first()->correo;
        $o = $this->show($orderId,$orders);
        $mail = Mail::to($correo)->send(new Compra($o,$correo));
    }
}
