<?php

namespace App\Http\Controllers\api;
use App\Models\Interfaces\DataResponse;
use App\Models\ProductosTallas;
use App\Models\RetirosIngresos;
use App\Models\Tallas;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use App\Models\Productos;
use App\Models\Categorias;
use App\Models\Descuentos;
use App\Models\Imagenes;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class ProductosController extends Controller
{
    /**
     * Función Original
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index(Request $request)
    // {
    //     $config = $request->all();
    //     $productos = null;
    //     if($request->has('key')) {
    //         $productos = Productos::where([
    //             [$config['key'],$config['operator'],$config['value']],
    //             ['status','=',1],
    //             ['stock','>',0]
    //         ])->with(['categorias.padre','images','tallas']);
    //     }else{
    //         $productos = Productos::where([
    //             ['status','=',1]
    //         ])->with(['categorias.padre','images','tallasF']);
    //     }

    //     return $productos->get();
    // }


    /**
     * Se añaden estas propiedades a la estructura existente.
     *  1.- producto.percentageDiscountApplied
     *  2.- producto.discountApplied
     *  3.- producto.priceWithoutDiscount
     *  4.- producto.discount
     *  5.- producto.categorias.descuento_sub_categoria
     *  6.- producto.categorias.padre.descuento_categorias
     *  7.-discountGeneral
     *  8.-discountCategory
     *  9.-discountSubCategory
     */

    public function index(Request $request)
    {
        $config = $request->all();
        $productos = null;
        if($request->has('key'))
        {
            $productos = Productos::where([
                [$config['key'],$config['operator'],$config['value']],
                ['status','=',1],
                ['stock','>',0]
            ])-> with(['discount'=> function ($dicount)
                {
                    $dicount->select(['id','idProduct','discount','reason','creation_date','finished_date']);
                } ,
                // Las categorias en realidad es la subcategoria que apunta a la categoria.
                'categorias' => function ($subCategory)
                {
                    $subCategory->with(['descuentoSubCategoria', 'padre'=>function ($category){
                        $category->with('descuentoCategorias');
                    }]);
                },
                'images','tallas','tallasF'
                ])->get();
        }
        else
        {
            $productos = Productos::where('status','=',1) ->with(['discount'=> function ($dicount)
                {
                    $dicount->select(['id','idProduct','discount','reason','creation_date','finished_date']);

                },
                // Las categorias en realidad es la subcategoria que apunta a la categoria.
                'categorias' => function ($subCategory)
                {
                    $subCategory->with(['descuentoSubCategoria', 'padre'=>function ($category){
                        $category->with('descuentoCategorias');
                    }]);
                },
                'images','tallasF'
                ])->get();
        }

        return  $this->applyDiscountProducts( $productos );
    }

    public function applyDiscountProducts( $articles ){

        $generalDiscount = app(\App\Http\Controllers\api\DescuentosController::class)->get_discount_general_all_people();

        return $articles -> reject(function ($article) use ($generalDiscount)
            {

                // Aplicar descuento general, descuento por categoria, descuento por subcategoria, descuento por articulo.

                $fullDiscount =  0;

                $article->purchasePrice = number_format( round($article->purchasePrice) , 2, '.', '');

                data_fill($article, 'priceWithoutDiscount', number_format( round($article->price) , 2, '.', '') );

                data_fill($article, 'discountGeneral', 0);
                data_fill($article, 'discountCategory', 0);
                data_fill($article, 'discountSubCategory', 0);


                // Descuento general
                if (count($generalDiscount))
                {
                    $article-> discountGeneral = $generalDiscount[0]-> discount;
                    $fullDiscount = $generalDiscount[0]-> discount;

                    $article->price = $article->price - ( $article->price * ( $article->discountGeneral / 100 ) );
                }

                // Descuento por categoria
                if (count($article->categorias->padre->descuentoCategorias))
                {
                    Arr::first($article->categorias->padre->descuentoCategorias, function ($discountCategory, $key) use (&$fullDiscount, &$article)
                    {
                        $fullDiscount += $discountCategory->discount;
                        $article->discountCategory = $discountCategory->discount;

                        $article->price = $article->price - ( $article->price * ( $article->discountCategory / 100 ) );
                    });
                }

                // Descuento por subcategoria
                if (count($article->categorias->descuentoSubCategoria))
                {
                    Arr::first($article->categorias->descuentoSubCategoria, function ($discountSubCategory, $key) use (&$fullDiscount, &$article)
                    {
                        $fullDiscount += $discountSubCategory->discount;
                        $article->discountSubCategory = $discountSubCategory->discount;

                        $article->price = $article->price - ( $article->price * ( $article->discountSubCategory / 100 ) );

                    });
                }

                // Descuento por articulo
                if (count($article->discount))
                {
                    Arr::first($article->discount, function ($discountArticle, $key) use (&$fullDiscount, &$article)
                    {
                        $fullDiscount += $discountArticle->discount;

                        $article->price = $article->price - ( $article->price * ( $discountArticle->discount / 100 ) );
                    });
                }

                $article->price = number_format( round($article->price) , 2, '.', '');

                data_fill($article, 'percentageDiscountApplied', $fullDiscount);

                data_fill($article, 'discountApplied', number_format( round($article->priceWithoutDiscount - $article->price) , 2, '.', '') );

            });
    }



    public function page(Request $request) {
        try{
            $config = $request->all();
            DB::beginTransaction();
            $productos = Productos::where([
                [$config['key'],$config['operator'],$config['value']],
                ['status','=',1],
                ['stock','>',0]
            ])->with(['categorias.padre','images','tallas']);
            $productos = $productos->paginate(10);
            DB::commit();
            return $productos;
        }catch (\Exception $e){
            DB::rollBack();
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_PAGE()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_PAGE()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            DB::beginTransaction();

            $producto = Productos::create($request->all());
            $data = [
                'idOrigin' => $producto->id,
                'type' => 'productos'
            ];
            $dataRequest = $request->all();
            if($dataRequest['tallas']) {
                $this->prepararTallas($producto->id,$request->get('tallas'));
            }
            if($request->has('image')) {
                $mainImage =  $request->all()['image'];
                $url = app(\App\Http\Controllers\api\ImagenesController::class)->upload($mainImage,'productos');
                app(\App\Http\Controllers\api\ImagenesController::class)->presSave($url, $data);
            }
            if($request->has('images')) {
                $images = $request->all()['images'];
                app(\App\Http\Controllers\api\ImagenesController::class)->uploadsAndSave($images,$data,'productos');
            }
            DB::commit();
            $response =  new DataResponse('Se ha agregado el producto correctamente','PROCESS_SUCESS',$producto);
            return response()->json($response,200);
        }catch (\Exception $e){
            DB::rollBack();
            $response = new DataResponse('Ha ocurrido un error al publicar su producto '.$e->getMessage(),'ERROR_CREATE',$request->all());
            return response()->json($response,505);
        }
    }

    private function  prepararTallas($idProducto,$tallas) {
        $productosTallas = array();
        foreach ($tallas as $talla){
                array_push($productosTallas,[
                    'tallas_id' => $talla['value'],
                    'productos_id' => $idProducto,
                    'cant' => $talla['cant']
                ]);
        }
        ProductosTallas::insert($productosTallas);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id){

        try{
            $producto = Productos::where([
                ['status','>',0],
                ['id','=',$id],
                ['stock','>',0]
            ])->with(['discount'=> function ($dicount)
            {
                $dicount->select(['id','idProduct','discount','reason','creation_date','finished_date']);
            },
            // Las categorias en realidad es la subcategoria que apunta a la categoria.
            'categorias' => function ($subCategory)
            {
                $subCategory->with(['descuentoSubCategoria', 'padre'=>function ($category){
                    $category->with('descuentoCategorias');
                }]);
            },'images','tallas'])-> get();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode(), $this->applyDiscountProducts( $producto )[0]);
            return response()->json($response);
        }catch (\Exception $e){
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $producto = Productos::where('id',$id);

            /**
             * ZurielDA
             *
             * Se reasigna nuevamente el precio sin descuento.
             * La propiedad "price" del objeto recibido contiene el precio con descuentos aplicados, estos descuentos son aplicados cuando se recuperan los productos del back hacia el front.
             */
            if($request->has('discountApplied') && $request-> discountApplied > 0)
            {
                $request->price = $request->priceWithoutDiscount;
            }

            /**
             *  ZurielDA
             *
             *  Se decartan los campos que fueron incluidos para la implementación de los descuentos.
             *  'creation_date', 'updated_date','discountGeneral','discountCategory', 'discountSubCategory', 'percentageDiscountApplied', 'discountApplied', 'discount', 'priceWithoutDiscount', 'imgAditionalCurrent'
             */
            $producto->update($request->except(['image','images','tallas','categorias','retiroIngreso', 'creation_date', 'updated_date', 'discountGeneral','discountCategory', 'discountSubCategory', 'percentageDiscountApplied', 'discountApplied', 'discount', 'priceWithoutDiscount', 'imgAditionalCurrent']));

            // ZurielDA
            if($request->has('imgAditionalCurrent')) {

                $defaultImageBase64 = $request->get('image');

                Arr::first($request->get('imgAditionalCurrent'), function ($dataImage) use ($defaultImageBase64)
                {
                    $idImage = Imagenes::where([ ['rel_id', '=', $dataImage['rel_id']],['status', '=', true],['uri_path', '=', $dataImage['url']]])->first();

                    switch ($dataImage['option'])
                    {
                        case 'current':
                                // No se hace nada.
                            break;

                        case 'delete':

                            app(\App\Http\Controllers\api\ImagenesController::class)->destroy($idImage->id);

                            break;

                        case 'new':

                            if ($dataImage['isDefault'])
                            {
                                $url = app(\App\Http\Controllers\api\ImagenesController::class)->upload($defaultImageBase64,'productos');

                                $newRequest = new Request();
                                $newRequest->merge(['uri_path' =>$url]);

                                app(\App\Http\Controllers\api\ImagenesController::class)->update($newRequest, $idImage->id);
                            }
                        break;
                    }
                });

                Arr::first($request->get('images'), function ($imageBase64) use ( $id )
                {
                    if ( !Str::of($imageBase64)->exactly('current') )
                    {
                        $url = app(\App\Http\Controllers\api\ImagenesController::class)->upload($imageBase64,'productos');
                        app(\App\Http\Controllers\api\ImagenesController::class)->presSave($url, ['idOrigin' => $id, 'type' => 'productos']);
                    }
                });
            }

            if($request->has('tallas')) {
                $this->updateStock($request->get('tallas'), $producto->first()->id);
            }

            if($request->has('retiroIngreso')) {
                $this->createRetiroIngreso($request->get('retiroIngreso'));
            }

            $response =  new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(),$producto->get());
            DB::commit();
            return response()->json($response);
        }catch (\Exception  $e) {
            DB::rollBack();
            $response =  new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),$e->getMessage());
            return response()->json($response,506);
        }
    }

    public function createRetiroIngreso($data) {
        try {
            RetirosIngresos::create($data);
        }catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    private function updateStock($tallas,$productoId) {
        ProductosTallas::where('productos_id',$productoId)->whereNotIn('tallas_id',collect($tallas)->map(
            function ($talla)  {
                return $talla['value'];
            }
        )->values())->delete();
        foreach ($tallas as $talla){
            $productoStock = ProductosTallas::where([
                ['tallas_id','=',$talla['value']],
                ['productos_id','=',$productoId]
            ]);
            if($productoStock->exists()) {
                $productoStock = $productoStock->update(
                    ['cant' => $talla['cant']]
                );
                if(!$productoStock) {
                    throw new \Exception('Error al actualizar las tallas'.json_encode($productoStock->first()));
                }
            }else {
                ProductosTallas::create([
                    'tallas_id' => $talla['value'],
                    'productos_id' => $productoId,
                    'cant' => $talla['cant']
                ]);
            }
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Productos::where('id',$id)->update([
            'status'=> false
        ]);
    }

    public function tallas() {
        try{
            DB::beginTransaction();
            DB::enableQueryLog();
            $tallas =  Tallas::where('status','>', 0)->select(
                '*','id as value'
            )->get();
            $queries = DB::getQueryLog();
            Log::info("Queries ->",$queries);
            return $tallas;
        }catch (\Exception $e) {
            DB::rollBack();
            $response =  new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),$e->getMessage());
            return response()->json($response,506);
        }
    }

    public function guardarTalla(Request $request) {
        try {
            $dataTalla = $request->all();
            DB::beginTransaction();
            $talla = Tallas::create($dataTalla);
            DB::commit();
            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$talla);
            return response()->json($response);
        }catch (\Exception $e){
            DB::rollBack();
            $response =  new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }

    public function destroyTalla($id)
    {
        try{
            DB::beginTransaction();
            $result = Tallas::where('id','=',$id)-> update(['status' => 0]);
            DB::commit();
            $response = new DataResponse('Se ha actualizado la talla',ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getCode(),$result);
            return response()->json($response);
        }catch (\Exception $e){
            $response = new DataResponse('Error al actualizar la talla',ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }

    public function updateTalla($id,Request $request,Tallas $talla) {
        try{
            DB::beginTransaction();
            $result = $talla->findOrFail($id);
            $result = $result->update($request->all());
            $response = new DataResponse('Se ha actualizado la talla',ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(),$result);
            DB::commit();
            return response()->json($response);
        }catch (\Exception $e){
            $response = new DataResponse('Error al actualizar la talla',ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }

    public function getRetirosIngresos(){
        $resultSet = RetirosIngresos::with('producto.images','talla')->get();
        return $resultSet;
    }

    public function resolveFilter($config, $ordersQuery = NULL) {
        $orders = Productos::from('productos as p')->where('p.status','=',1)->select(
            '*',
            DB::raw('date(updated_date) as fecha_actualizacion'),
            DB::raw('date(creation_date) as fecha_creacion')
        );
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

}
