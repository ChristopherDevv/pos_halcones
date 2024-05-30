<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\api\ImagenesController;
use App\Models\Categorias;
use App\Models\Descuentos;
use App\Models\Interfaces\DataResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CategoriasController extends Controller
{
    public function index(Request  $request)
    {
        $generalDiscount = app(\App\Http\Controllers\api\DescuentosController::class)->get_discount_general_all_people();

        $categorias =  Categorias::where([
            ['status','=',1],
            ['padre_id','=',NULL]
        ])->with(['descuentoCategorias'=> function($descuentoCategorias){

            $descuentoCategorias-> select(['id', 'idCategory', 'discount', 'reason']);

        },'subcategories'=>function ($subcategories)
        {
            $subcategories->with(['descuentoSubCategoria'=>function($descuentoSubCategoria){

                $descuentoSubCategoria->select([ 'id', 'idSubCategory', 'discount', 'reason']);

            }, 'productos'=> function($productos)
            {
                $productos->with(['discount'=>function($discount){

                    $discount->select(['id', 'idProduct', 'discount', 'reason']);

                }]);

            }]);

        },'image'])->get() -> reject(function ($category) use ($generalDiscount)
        {

            if ($category-> image)
            {

                $category-> image-> makeVisible('id');
            }

            $fullDiscount = count($generalDiscount) ? $generalDiscount[0]-> discount : 0;


            /**
             * Se extrae cada descuento de la categoria
             */
            $discountCategoryAux = collect([]);

            if (count($category->descuentoCategorias))
            {
                Arr::first($category->descuentoCategorias, function ($discountCategory, $key) use (&$fullDiscount, &$discountCategoryAux)
                {
                    // Se suma el descuento de categoria al descuento total
                    $fullDiscount += $discountCategory->discount;

                    $discountCategoryAux->push($discountCategory->discount);
                });
            }
            /***/


            /**
             * Se trabaja sobre subcategoria, el cual contiene cada descuento y la lista de productos.
             */
            $category->subcategories = $category->subcategories->map(function ($subcategory) use (&$fullDiscount, $generalDiscount, $discountCategoryAux)
            {
                /**
                 * Se extrae cada descuento de la subcategoria
                 */
                $descuentoSubCategoriaAux = collect([]);

                if (count($subcategory->descuentoSubCategoria))
                {
                    Arr::first($subcategory->descuentoSubCategoria, function ($discountSubCategory, $key) use (&$fullDiscount, &$descuentoSubCategoriaAux)
                    {
                        // Se suma el descuento de categoria al descuento total
                        $fullDiscount += $discountSubCategory->discount;

                        $descuentoSubCategoriaAux->push($discountSubCategory->discount);
                    });
                }

                /**
                 * Se trabaja sobre el producto del cual se obtiene su descuento (si existe) y se aplica el descuento total.
                 * Se añade propiedades relacionadas con el descuento aplicado.
                 */
                $articles = collect([]);

                if (count($subcategory->productos))
                {
                    Arr::first($subcategory->productos, function ($article, $key) use ($generalDiscount, &$articles, &$fullDiscount, $discountCategoryAux, $descuentoSubCategoriaAux)
                    {
                        data_fill($article, 'priceWithoutDiscount', number_format( round($article->price) , 2, '.', ''));

                        data_fill($article, 'discountGeneral', 0);
                        data_fill($article, 'discountCategory', 0);
                        data_fill($article, 'discountSubCategory', 0);

                        // Descuento general
                        if (count($generalDiscount))
                        {
                            $article-> discountGeneral = $generalDiscount[0]-> discount;

                            $article->price = $article->price - ( $article->price * ( $article->discountGeneral / 100 ) );
                        }
                        else
                        {
                            $article-> discountGeneral = 0;
                        }

                        // Descuento por categoría
                        if ($discountCategoryAux->count()) {

                            $article->discountCategory = $discountCategoryAux->first();

                            $article->price = $article->price - ( $article->price * ( $article->discountCategory / 100 ) );
                        }
                        else
                        {
                            $article->discountCategory = 0;
                        }

                        // Descuento por subcategoría
                        if ($descuentoSubCategoriaAux->count()) {

                            $article->discountSubCategory = $descuentoSubCategoriaAux->first();

                            $article->price = $article->price - ( $article->price * ( $article->discountSubCategory / 100 ) );
                        }
                        else
                        {
                            $article->discountSubCategory = 0;
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

                        $article->price = number_format( round($article->price) , 2, '.', '');
                        $article->purchasePrice = number_format( round($article->purchasePrice) , 2, '.', '');

                        $articles->push($article);
                    });

                    $subcategory->productos = $articles;
                }

                return $subcategory;
            });

        });

        $categorias = collect($categorias)->map(
            function ($categoria) use ($request){
                if(!$request->has('isAdmin')) {
                    $aux = $categoria->subcategories->filter(
                        function ($sub) {
                            return $sub->productos->isNotEmpty();
                        }
                    )->values();
                    unset($categoria->subcategories);
                    $categoria->subcategories = $aux;
                }
                if(!is_null($categoria->image)) {
                    if(Storage::disk('productos')->exists( $categoria->image->uri_path)) {
                        $categoria->image->encrypted = base64_encode(file_get_contents(public_path() . $categoria->image->uri_path));
                    }
                }

                return $categoria;
            }
        );
        if(!$request->has('isAdmin')) {
            $categorias = collect($categorias)->filter(
                function ($categoria) {
                    return $categoria->subcategories->isNotEmpty();
                }
            )->values();
        }
        return $categorias;
    }

    public function subCategorias() {
        $categorias =  Categorias::where([
            ['status','=',true],
            ['padre_id','<>',NULL]
        ])->with('padre')->get();
        return $categorias;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categorias = Categorias::where('status',true)->get();
        return $categorias;
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
            $categoria = $request->only(['title','value']);
            $request->validate(['title'=>'nullable|required','value' => 'nullable|required']);
            $subcategorias = $request->all()['subcategories'];
            $resultSet = Categorias::create($categoria);
            if($request->has('image')) {
                $url = app(ImagenesController::class)->upload($request->get('image'),'productos');
                $data = [
                    'idOrigin' => $resultSet->id,
                    'type' => 'categoria'
                ];
                app(ImagenesController::class)->presSave($url,$data);
            }
            $this->prepareSubCategories($subcategorias,$resultSet->id);
            DB::commit();
            $response =  new DataResponse('Se han guardado los datos exitosamente', 'PROCESS_SUCESS',$categoria);
            return response()->json($response);
        }catch (\Exception $e){
            DB::rollBack();
            $response =  new DataResponse('Ha ocurrido un error'.$e->getMessage(), 'PROCESS_ERROR',$e->getTrace());
            return response()->json($response,505);
        }
    }

    public function  prepareSubCategories($categories, $idOrigin) {
        $subs = array();
        foreach ($categories as $category){
            if(!isset($category['id'])) {
                $category['padre_id'] = $idOrigin;
                array_push($subs,$category);
            }else if(isset($category['id'])) {

            }
        }
        Categorias::insert($subs);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Categorias  $categorias
     * @return \Illuminate\Http\Response
     */
    public function show(Categorias $categorias)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Categorias  $categorias
     * @return \Illuminate\Http\Response
     */
    public function edit(Categorias $categorias)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Categorias  $categorias
     * @return \Illuminate\Http\Response
     */
    public function update($id,Request $request, Categorias $categorias)
    {
        try {
            DB::beginTransaction();

            $id_padre = $request->only('id');

            $subs = $request->input('subcategories');

            $subcategories  = $categorias::where('id',$request->only('id'))->with('subcategories')->first();

            $oldSusb = collect($subcategories->subcategories);

            $oldSusb-> filter(function($valueOldSubCategory, $key) use ($categorias, &$subs, $id_padre) {

                $subcategory = Arr::where($subs, function ($valueCurrentSubCategory, $key) use ($categorias, $valueOldSubCategory, &$subs, $id_padre) {

                    if (!$valueCurrentSubCategory['id']) {

                        $valueCurrentSubCategory['padre_id'] = $id_padre['id'];

                        if ($categorias->create($valueCurrentSubCategory)) {

                            Arr::forget($subs, $key);
                        }
                    }

                    return $valueOldSubCategory-> id == $valueCurrentSubCategory['id'] && !Str::of($valueOldSubCategory-> value)->exactly($valueCurrentSubCategory['value']);
                });

                if ($subcategory) {

                    $element = head( Arr::except($subcategory, 'id') );

                    $valueOldSubCategory-> update( $element );
                }
            });

            $oldSusb-> each( function($valueOldSubcategory, $keyOldSubcategory) use ($subs) {

                $isFound = Arr::first($subs, function ($valueCurrentSubcategory, $keyCurrentSubcategory) use ($valueOldSubcategory){

                    return $valueOldSubcategory-> id == $valueCurrentSubcategory['id'];
                });

                if (!$isFound) {

                    $valueOldSubcategory->delete();
                }
            });


            if($request->has('image')) {

                // Se remplaza la imagen
                if (!Str::contains($request->get('image'), 'imgCurrent')) {

                    $img = [
                        'id' => null,
                        'rel_type' => 'categoria',
                        'rel_id' =>  $request->get('id')
                    ];
                    app(\App\Http\Controllers\api\ImagenesController::class)->replaceImage($request->get('image'),$img,'productos');

                }
            }
            $categorias->where('id',$request->get('id'))->update(
                $request->except('id','subcategories','image', 'idImage')
            );
            DB::commit();
            $response =  new DataResponse('Se han guardado los datos exitosamente', 'PROCESS_SUCESS',$subcategories);
            return response()->json($response);
        }catch (\Exception $e){
            DB::rollBack();
            $response =  new DataResponse('Ha ocurrido un error'.$e->getMessage(), 'PROCESS_ERROR',$e->getTrace());
            return response()->json($response,505);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Categorias  $categorias
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Categorias $categorias)
    {
        try {
            DB::beginTransaction();
            $resultSet = $categorias->where('id',$id)->delete();
            DB::commit();
            $response =  new DataResponse('Se ha eliminado el modelo', 'DELETE_PROCESS_SUCESS',$resultSet);
            return response()->json($response);
        }catch (\Exception $e) {
            DB::rollBack();
            $response =  new DataResponse('Ha ocurrido un error'.$e->getMessage(), 'DELETE_PROCESS_ERROR',$e->getTrace());
            return response()->json($response,505);
        }
    }
}
