<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Descuentos;
use Illuminate\Support\Carbon;




class DescuentosController extends Controller
{
    public function get_discount_general_all_people(){

        return Descuentos::where([
            ['idCategory', '=', null ],
            ['idSubCategory', '=', null],
            ['idProduct', '=', null],
            ['idMemberShip', '=', null],
            ['status', '=', 'Activo']
        ])->get();

    }

    public function create_all_people(Request  $request)
    {
        try
        {
            $idUser = $request->get('idUser');
            $idDiscountGeneral = $request->get('idDiscountGeneral');
            $discountGeneral = $request->get('discountGeneral');
            $generalReason = $request->get('generalReason');
            $generalCompletationDate = $request->get('generalCompletationDate');

            $discountCategories = $request->get('discountCategories');
            $discountSubCategories = $request->get('discountSubCategories');
            $discountArticles = $request->get('discountArticles');

            $saveDiscountGeneral = null;
            $saveDiscountCategories = [];
            $saveDiscountSubCategories = [];
            $saveDiscountArticles = [];

            /**
             * Registro de descuento general.
             **/

                // Se comprueba que la información halla cambiado respecto a la que se tiene registrada en la base de datos.
                    $isSameDiscountGeneral = false;

                    if ($idDiscountGeneral)
                    {
                        $discount = Descuentos::find($idDiscountGeneral);

                        // Carbon::createFromDate($discount-> finished_date)->toArray()["formatted"];

                        if ($discount-> discount == $discountGeneral && $discount-> reason == $generalReason && $discount-> finished_date == $generalCompletationDate)
                        {
                            $isSameDiscountGeneral = true;
                        }
                    }

                // Si no se tiene valor de descuento y no es un descuento existente, se inactiva el descuento general activo existente.

                    if (!$discountGeneral && !$isSameDiscountGeneral)
                    {
                        Descuentos::where([
                            ['idCategory', '=', null ],
                            ['idSubCategory', '=', null],
                            ['idProduct', '=', null],
                            ['idMemberShip', '=', null],
                            ['status', '=', 'Activo']
                        ])->update(['status' => 'Inactivo']);
                    }

                // Si se tiene valor de descuento y la información es distinta a un descuento existente se crea el nuevo descuento.

                    if($discountGeneral && !$isSameDiscountGeneral)
                    {
                        try {

                            // Se inactiva la existencia de un descuento general existe
                            Descuentos::where([
                                ['idCategory', '=', null ],
                                ['idSubCategory', '=', null],
                                ['idProduct', '=', null],
                                ['idMemberShip', '=', null],
                                ['status', '=', 'Activo']
                            ])->update(['status' => 'Inactivo']);

                            // Se registrael nuevo descuento
                            $discountGeneralApply = new Descuentos;

                            $discountGeneralApply-> idUser = $idUser;
                            $discountGeneralApply-> discount = $discountGeneral;
                            $discountGeneralApply-> reason = $generalReason;
                            $discountGeneralApply-> finished_date = $generalCompletationDate;


                            $discountGeneralApply->save();

                            $saveDiscountGeneral = '{
                                "status":true,
                                "discount": '.$discountGeneralApply.'
                            }';

                        } catch (Throwable $e) {
                            report($e);

                            $saveDiscountGeneral = '{
                                "status":false,
                                "discount": '.$discountGeneralApply.'
                            }';
                        }
                    }

            /**
             * Registro de descuento por categoria.
             **/

                // Se inactivan todos los descuentos por categorias
                    if (count($discountCategories) < 1)
                    {
                        Descuentos::where([
                            ['idCategory', '!=', null ],
                            ['idMemberShip', '=', null],
                            ['status', '=', 'Activo']
                        ])->update(['status' => 'Inactivo']);
                    }

                // Se inactivan todos los descuentos por categorias que no se encuentran en el arreglo.
                    if (count($discountCategories) > 0)
                    {
                        $getCategories = [];

                        Arr::first($discountCategories, function ($category, $key) use (&$getCategories)
                        {
                            $getCategories = Arr::add($getCategories, $key, $category['category']);
                        });

                        Descuentos::where([
                            ['idCategory', '!=', null ],
                            ['idMemberShip', '=', null],
                            ['status', '=', 'Activo']
                        ])->get()->reject(function ($value, $key) use ($getCategories)
                        {
                            $isFind = Arr::first($getCategories, function ($element, $key) use ($value)
                            {
                                return $element == $value->idCategory;
                            });

                            if (!$isFind)
                            {
                                $discount = Descuentos::find($value->id);

                                $discount->status = 'Inactivo';

                                $discount->save();
                            }
                        });
                    }


                // Se registra descuento por cada categoria
                    Arr::first($discountCategories, function ($category, $key) use ($idUser, &$saveDiscountCategories)
                    {

                            // Se comprueba que la información halla cambiado respecto a la que se tiene registrada en la base de datos.

                            $isSameDiscountCategories = false;

                            if ($category['id'])
                            {
                                $discount = Descuentos::find($category['id']);

                                if ($discount-> discount == $category['categoryDiscount'] && Str::of($discount-> reason)->exactly($category['categoryReason'])  &&  Str::of($discount-> status)->exactly('Activo') && $discount-> finished_date == $category['categoryCompletationDate'])
                                {
                                    $isSameDiscountCategories = true;
                                }
                            }


                                // Se hace el registro si la información es distinta a la que esta en la base de datos.

                                if (!$isSameDiscountCategories)
                                {
                                    try {
                                        /**
                                         * Se inactiva la existencia de un descuento por categoria antes del registro del nuevo.
                                         * Por si llega a fallar la comprobacion del front
                                         * */
                                        Descuentos::where([
                                            ['idCategory', '=', $category['category']],
                                            ['idMemberShip', '=', null],
                                            ['status', '=', 'Activo']
                                        ])->update(['status' => 'Inactivo']);

                                        // Se registrael nuevo descuento
                                        $discount = new Descuentos;

                                        $discount-> idUser = $idUser;
                                        $discount-> idCategory = $category['category'];
                                        $discount-> discount = $category['categoryDiscount'];
                                        $discount-> reason = $category['categoryReason'];
                                        $discount-> finished_date = $category['categoryCompletationDate'];

                                        $discount->save();

                                        $saveDiscountCategories = Arr::add($saveDiscountCategories, $category['category'], '{
                                            "status":true,
                                            "discount":'.$discount.'
                                        }');


                                    } catch (Throwable $e) {
                                        report($e);

                                        $saveDiscountCategories = Arr::add($saveDiscountCategories, $category['category'], '{
                                            "status":false,
                                            "discount":null
                                        }');
                                    }
                                }
                    });


            /**
             * Registro de descuento por subcategoria.
             **/

                // Se inactivan todos los descuentos por subcategorias.
                    if (count($discountSubCategories) < 1)
                    {
                        Descuentos::where([
                            ['idSubCategory', '!=', null ],
                            ['idMemberShip', '=', null],
                            ['status', '=', 'Activo']
                        ])->update(['status' => 'Inactivo']);
                    }


                // Se inactivan todos los descuentos por categorias que no se encuentran en el arreglo.
                    if (count($discountSubCategories) > 0)
                    {
                        $getSubCategories = [];

                        Arr::first($discountSubCategories, function ($subCategory, $key) use (&$getSubCategories)
                        {
                            $getSubCategories = Arr::add($getSubCategories, $key, $subCategory['subCategory']);
                        });

                        Descuentos::where([
                            ['idSubCategory', '!=', null ],
                            ['idMemberShip', '=', null],
                            ['status', '=', 'Activo']
                        ])->get()->reject(function ($value, $key) use ($getSubCategories)
                        {
                            $isFind = Arr::first($getSubCategories, function ($element, $key) use ($value)
                            {
                                return $element == $value->idSubCategory;
                            });

                            if (!$isFind)
                            {
                                $discount = Descuentos::find($value->id);

                                $discount->status = 'Inactivo';

                                $discount->save();
                            }
                        });
                    }

                // Se registra descuento por subcategoria
                Arr::first($discountSubCategories, function ($subCategory, $key) use ($idUser, &$saveDiscountSubCategories )
                {
                    /**
                     * Se comprueba que la información halla cambiado respecto a la que se tiene registrada en la base de datos.
                     */
                    $isSameDiscountSubCategories = false;

                    if ($subCategory['id'])
                    {
                        $discount = Descuentos::find($subCategory['id']);

                        if ($discount-> discount == $subCategory['subCategoryDiscount'] && Str::of($discount-> reason)->exactly($subCategory['subCategoryReason'])  &&  Str::of($discount-> status)->exactly('Activo') && $discount-> finished_date == $subCategory['subCategoryCompletationDate'])
                        {
                            $isSameDiscountSubCategories = true;
                        }
                    }

                    /**
                     * Se hace el registro si la información es distinta a la que esta en la base de datos.
                     */
                    if (!$isSameDiscountSubCategories)
                    {
                        try {
                            /**
                             * Se inactiva la existencia de un descuento general antes del registro del nuevo.
                             * Por si llega a fallar la comprobacion del front
                            * */
                            Descuentos::where([
                                ['idSubCategory', '=', $subCategory['subCategory']],
                                ['idMemberShip', '=', null],
                                ['status', '=', 'Activo']
                            ])->update(['status' => 'Inactivo']);

                            // Se registrael nuevo descuento
                            $discount = new Descuentos;

                            $discount-> idUser = $idUser;
                            $discount-> idSubCategory = $subCategory['subCategory'];
                            $discount-> discount = $subCategory['subCategoryDiscount'];
                            $discount-> reason = $subCategory['subCategoryReason'];
                            $discount-> finished_date = $subCategory['subCategoryCompletationDate'];

                            $discount->save();

                            $saveDiscountSubCategories = Arr::add($saveDiscountSubCategories, $subCategory['subCategory'], '{
                                "status":true,
                                "discount":'.$discount.'
                            }');


                        } catch (Throwable $e) {
                            report($e);

                            $saveDiscountSubCategories = Arr::add($saveDiscountSubCategories, $subCategory['subCategory'], '{
                                "status":false,
                                "discount":null
                            }');
                        }
                    }
                });


            /**
             * Registro de descuento por articulos.
             **/

                if (count($discountArticles) < 1)
                {
                    Descuentos::where([
                        ['idProduct', '!=', null ],
                        ['idMemberShip', '=', null],
                        ['status', '=', 'Activo']
                    ])->update(['status' => 'Inactivo']);
                }

                // Se inactivan todos los descuentos por categorias que no se encuentran en el arreglo.
                if (count($discountArticles) > 0)
                {
                    $getArticles = [];

                    Arr::first($discountArticles, function ($article, $key) use (&$getArticles)
                    {
                        $getArticles = Arr::add($getArticles, $key, $article['article']);
                    });

                    Descuentos::where([
                        ['idProduct', '!=', null ],
                        ['idMemberShip', '=', null],
                        ['status', '=', 'Activo']
                    ])->get()->reject(function ($value, $key) use ($getArticles)
                    {

                        $isFind = Arr::first($getArticles, function ($element, $key) use ($value)
                        {
                            return $element == $value->idProduct;
                        });

                        if (!$isFind)
                        {
                            $discount = Descuentos::find($value->id);

                            $discount->status = 'Inactivo';

                            $discount->save();
                        }
                    });
                }

                // Se registra descuento por articulo
                Arr::first($discountArticles, function ($article, $key) use ($idUser, &$saveDiscountArticles)
                {
                    /**
                     * Se comprueba que la información halla cambiado respecto a la que se tiene registrada en la base de datos.
                     */
                    $isSameDiscountArticles = false;

                    if ($article['id'])
                    {
                        $discount = Descuentos::find($article['id']);

                        if ($discount-> discount == $article['articleDiscount'] && Str::of($discount-> reason)->exactly($article['articleReason'])  &&  Str::of($discount-> status)->exactly('Activo') && $discount-> finished_date == $article['articleCompletationDate'])
                        {
                            $isSameDiscountArticles = true;
                        }
                    }

                    /**
                     * SeSe hace el registro si la información es distinta a la que esta en la base de datos.
                     */
                    if (!$isSameDiscountArticles)
                    {
                        try {
                            /**
                             * Se inactiva la existencia de un descuento general antes del registro del nuevo.
                             * Por si llega a fallar la comprobacion del front
                             * */
                            Descuentos::where([
                                ['idProduct', '=', $article['article']],
                                ['idMemberShip', '=', null],
                                ['status', '=', 'Activo']
                            ])->update(['status' => 'Inactivo']);

                            $discount = new Descuentos;

                            $discount->idUser = $idUser;
                            $discount->idProduct = $article['article'];
                            $discount->discount = $article['articleDiscount'];
                            $discount->reason = $article['articleReason'];
                            $discount-> finished_date = $article['articleCompletationDate'];

                            $discount->save();


                            $saveDiscountArticles = Arr::add($saveDiscountArticles, $article['article'], '{
                                "status":true,
                                "discount":'.$discount.'
                            }');


                        } catch (Throwable $e) {
                            report($e);

                            $saveDiscountArticles = Arr::add($saveDiscountArticles, $article['article'], '{
                                "status":false,
                                "discount":null
                            }');
                        }
                    }
                });


            return response()->json(
            [
                'status' => true,
                'DiscountGeneral' => $saveDiscountGeneral,
                'DiscountCategories' => $saveDiscountCategories,
                'DiscountSubCategories' => $saveDiscountSubCategories,
                'DiscountArticles' => $saveDiscountArticles

            ], 202);
        }
        catch (Throwable $e)
        {
            report($e);

            return response()->json(
                [
                    'status' => false,
                    'DiscountGeneral' => $saveDiscountGeneral,
                    'DiscountCategories' => $saveDiscountCategories,
                    'DiscountSubCategories' => $saveDiscountSubCategories,
                    'DiscountArticles' => $saveDiscountArticles

                ], 500);
        }
    }

    public function create_membership(Request  $request)
    {

        try
        {
            $idMemberShip = $request->get('idMemberShip');
            $idUser = $request->get('idUser');
            $idDiscountGeneral = $request->get('idDiscountGeneral');
            $discountGeneral = $request->get('discountGeneral');
            $generalReason = $request->get('generalReason');
            $generalCompletationDate = $request->get('generalCompletationDate');

            $discountCategories = $request->get('discountCategories');
            $discountSubCategories = $request->get('discountSubCategories');
            $discountArticles = $request->get('discountArticles');

            $saveDiscountGeneral = null;
            $saveDiscountCategories = [];
            $saveDiscountSubCategories = [];
            $saveDiscountArticles = [];

            /**
             * Registro de descuento general.
             **/

                // Se comprueba que la información halla cambiado respecto a la que se tiene registrada en la base de datos.
                    $isSameDiscountGeneral = false;

                    if ($idDiscountGeneral)
                    {
                        $discount = Descuentos::find($idDiscountGeneral);

                        // Carbon::createFromDate($discount-> finished_date)->toArray()["formatted"];

                        if ($discount-> discount == $discountGeneral && $discount-> reason == $generalReason && $discount-> finished_date == $generalCompletationDate)
                        {
                            $isSameDiscountGeneral = true;
                        }
                    }

                // Si no se tiene valor de descuento y no es un descuento existente, se inactiva el descuento general activo existente.

                    if (!$discountGeneral && !$isSameDiscountGeneral)
                    {
                        Descuentos::where([
                            ['idCategory', '=', null ],
                            ['idSubCategory', '=', null],
                            ['idProduct', '=', null],
                            ['idMemberShip', '=', $idMemberShip],
                            ['status', '=', 'Activo']
                        ])->update(['status' => 'Inactivo']);
                    }

                // Si se tiene valor de descuento y la información es distinta a un descuento existente se crea el nuevo descuento.

                    if($discountGeneral && !$isSameDiscountGeneral)
                    {
                        try {

                            // Se inactiva la existencia de un descuento general existe
                            Descuentos::where([
                                ['idCategory', '=', null ],
                                ['idSubCategory', '=', null],
                                ['idProduct', '=', null],
                                ['idMemberShip', '=', $idMemberShip],
                                ['status', '=', 'Activo']
                            ])->update(['status' => 'Inactivo']);

                            // Se registrael nuevo descuento
                            $discountGeneralApply = new Descuentos;

                            $discountGeneralApply-> idMemberShip = $idMemberShip;
                            $discountGeneralApply-> idUser = $idUser;
                            $discountGeneralApply-> discount = $discountGeneral;
                            $discountGeneralApply-> reason = $generalReason;
                            $discountGeneralApply-> finished_date = $generalCompletationDate;

                            $discountGeneralApply->save();

                            $saveDiscountGeneral = '{
                                "status":true,
                                "discount": '.$discountGeneralApply.'
                            }';

                        } catch (Throwable $e) {
                            report($e);

                            $saveDiscountGeneral = '{
                                "status":false,
                                "discount": '.$discountGeneralApply.'
                            }';
                        }
                    }

            /**
             * Registro de descuento por categoria.
             **/

                // Se inactivan todos los descuentos por categorias
                    if (count($discountCategories) < 1)
                    {
                        Descuentos::where([
                            ['idCategory', '!=', null ],
                            ['idMemberShip', '=', $idMemberShip],
                            ['status', '=', 'Activo']
                        ])->update(['status' => 'Inactivo']);
                    }

                // Se inactivan todos los descuentos por categorias que no se encuentran en el arreglo.
                    if (count($discountCategories) > 0)
                    {
                        $getCategories = [];

                        Arr::first($discountCategories, function ($category, $key) use (&$getCategories)
                        {
                            $getCategories = Arr::add($getCategories, $key, $category['category']);
                        });

                        Descuentos::where([
                            ['idCategory', '!=', null ],
                            ['idMemberShip', '=', $idMemberShip],
                            ['status', '=', 'Activo']
                        ])->get()->reject(function ($value, $key) use ($getCategories)
                        {
                            $isFind = Arr::first($getCategories, function ($element, $key) use ($value)
                            {
                                return $element == $value->idCategory;
                            });

                            if (!$isFind)
                            {
                                $discount = Descuentos::find($value->id);

                                $discount->status = 'Inactivo';

                                $discount->save();
                            }
                        });
                    }


                // Se registra descuento por cada categoria
                    Arr::first($discountCategories, function ($category, $key) use ($idMemberShip, $idUser, &$saveDiscountCategories)
                    {

                            // Se comprueba que la información halla cambiado respecto a la que se tiene registrada en la base de datos.

                            $isSameDiscountCategories = false;

                            if ($category['id'])
                            {
                                $discount = Descuentos::find($category['id']);

                                if ($discount-> discount == $category['categoryDiscount'] && Str::of($discount-> reason)->exactly($category['categoryReason'])  &&  Str::of($discount-> status)->exactly('Activo') && $discount-> finished_date == $category['categoryCompletationDate'])
                                {
                                    $isSameDiscountCategories = true;
                                }
                            }

                                // Se hace el registro si la información es distinta a la que esta en la base de datos.

                                if (!$isSameDiscountCategories)
                                {
                                    try {
                                        /**
                                         * Se inactiva la existencia de un descuento por categoria antes del registro del nuevo.
                                         * Por si llega a fallar la comprobacion del front
                                         * */
                                        Descuentos::where([
                                            ['idCategory', '=', $category['category']],
                                            ['idMemberShip', '=', $idMemberShip],
                                            ['status', '=', 'Activo']
                                        ])->update(['status' => 'Inactivo']);

                                        // Se registrael nuevo descuento
                                        $discount = new Descuentos;

                                        $discount-> idMemberShip = $idMemberShip;
                                        $discount-> idUser = $idUser;
                                        $discount-> idCategory = $category['category'];
                                        $discount-> discount = $category['categoryDiscount'];
                                        $discount-> reason = $category['categoryReason'];
                                        $discount-> finished_date = $category['categoryCompletationDate'];

                                        $discount->save();

                                        $saveDiscountCategories = Arr::add($saveDiscountCategories, $category['category'], '{
                                            "status":true,
                                            "discount":'.$discount.'
                                        }');


                                    } catch (Throwable $e) {
                                        report($e);

                                        $saveDiscountCategories = Arr::add($saveDiscountCategories, $category['category'], '{
                                            "status":false,
                                            "discount":null
                                        }');
                                    }
                                }
                    });


            /**
             * Registro de descuento por subcategoria.
             **/

                // Se inactivan todos los descuentos por subcategorias.
                    if (count($discountSubCategories) < 1)
                    {
                        Descuentos::where([
                            ['idSubCategory', '!=', null ],
                            ['idMemberShip', '=', $idMemberShip],
                            ['status', '=', 'Activo']
                        ])->update(['status' => 'Inactivo']);
                    }


                // Se inactivan todos los descuentos por categorias que no se encuentran en el arreglo.
                    if (count($discountSubCategories) > 0)
                    {
                        $getSubCategories = [];

                        Arr::first($discountSubCategories, function ($subCategory, $key) use (&$getSubCategories)
                        {
                            $getSubCategories = Arr::add($getSubCategories, $key, $subCategory['subCategory']);
                        });

                        Descuentos::where([
                            ['idSubCategory', '!=', null ],
                            ['idMemberShip', '=', $idMemberShip],
                            ['status', '=', 'Activo']
                        ])->get()->reject(function ($value, $key) use ($getSubCategories)
                        {
                            $isFind = Arr::first($getSubCategories, function ($element, $key) use ($value)
                            {
                                return $element == $value->idSubCategory;
                            });

                            if (!$isFind)
                            {
                                $discount = Descuentos::find($value->id);

                                $discount->status = 'Inactivo';

                                $discount->save();
                            }
                        });
                    }

                // Se registra descuento por subcategoria
                Arr::first($discountSubCategories, function ($subCategory, $key) use ($idMemberShip, $idUser, &$saveDiscountSubCategories )
                {
                    /**
                     * Se comprueba que la información halla cambiado respecto a la que se tiene registrada en la base de datos.
                     */
                    $isSameDiscountSubCategories = false;

                    if ($subCategory['id'])
                    {
                        $discount = Descuentos::find($subCategory['id']);

                        if ($discount-> discount == $subCategory['subCategoryDiscount'] && Str::of($discount-> reason)->exactly($subCategory['subCategoryReason'])  &&  Str::of($discount-> status)->exactly('Activo') && $discount-> finished_date == $subCategory['subCategoryCompletationDate'])
                        {
                            $isSameDiscountSubCategories = true;
                        }
                    }

                    /**
                     * Se hace el registro si la información es distinta a la que esta en la base de datos.
                     */
                    if (!$isSameDiscountSubCategories)
                    {
                        try {
                            /**
                             * Se inactiva la existencia de un descuento general antes del registro del nuevo.
                             * Por si llega a fallar la comprobacion del front
                            * */
                            Descuentos::where([
                                ['idSubCategory', '=', $subCategory['subCategory']],
                                ['idMemberShip', '=', $idMemberShip],
                                ['status', '=', 'Activo']
                            ])->update(['status' => 'Inactivo']);

                            // Se registrael nuevo descuento
                            $discount = new Descuentos;

                            $discount-> idMemberShip = $idMemberShip;
                            $discount-> idUser = $idUser;
                            $discount-> idSubCategory = $subCategory['subCategory'];
                            $discount-> discount = $subCategory['subCategoryDiscount'];
                            $discount-> reason = $subCategory['subCategoryReason'];
                            $discount-> finished_date = $subCategory['subCategoryCompletationDate'];

                            $discount->save();

                            $saveDiscountSubCategories = Arr::add($saveDiscountSubCategories, $subCategory['subCategory'], '{
                                "status":true,
                                "discount":'.$discount.'
                            }');


                        } catch (Throwable $e) {
                            report($e);

                            $saveDiscountSubCategories = Arr::add($saveDiscountSubCategories, $subCategory['subCategory'], '{
                                "status":false,
                                "discount":null
                            }');
                        }
                    }
                });


            /**
             * Registro de descuento por articulos.
             **/

                if (count($discountArticles) < 1)
                {
                    Descuentos::where([
                        ['idProduct', '!=', null ],
                        ['idMemberShip', '=', $idMemberShip],
                        ['status', '=', 'Activo']
                    ])->update(['status' => 'Inactivo']);
                }

                // Se inactivan todos los descuentos por categorias que no se encuentran en el arreglo.
                if (count($discountArticles) > 0)
                {
                    $getArticles = [];

                    Arr::first($discountArticles, function ($article, $key) use (&$getArticles)
                    {
                        $getArticles = Arr::add($getArticles, $key, $article['article']);
                    });

                    Descuentos::where([
                        ['idProduct', '!=', null ],
                        ['idMemberShip', '=', $idMemberShip],
                        ['status', '=', 'Activo']
                    ])->get()->reject(function ($value, $key) use ($getArticles)
                    {

                        $isFind = Arr::first($getArticles, function ($element, $key) use ($value)
                        {
                            return $element == $value->idProduct;
                        });

                        if (!$isFind)
                        {
                            $discount = Descuentos::find($value->id);

                            $discount->status = 'Inactivo';

                            $discount->save();
                        }
                    });
                }

                // Se registra descuento por articulo
                Arr::first($discountArticles, function ($article, $key) use ($idMemberShip, $idUser, &$saveDiscountArticles)
                {
                    /**
                     * Se comprueba que la información halla cambiado respecto a la que se tiene registrada en la base de datos.
                     */
                    $isSameDiscountArticles = false;

                    if ($article['id'])
                    {
                        $discount = Descuentos::find($article['id']);

                        if ($discount-> discount == $article['articleDiscount'] && Str::of($discount-> reason)->exactly($article['articleReason'])  &&  Str::of($discount-> status)->exactly('Activo') && $discount-> finished_date == $article['articleCompletationDate'])
                        {
                            $isSameDiscountArticles = true;
                        }
                    }

                    /**
                     * SeSe hace el registro si la información es distinta a la que esta en la base de datos.
                     */
                    if (!$isSameDiscountArticles)
                    {
                        try {
                            /**
                             * Se inactiva la existencia de un descuento general antes del registro del nuevo.
                             * Por si llega a fallar la comprobacion del front
                             * */
                            Descuentos::where([
                                ['idProduct', '=', $article['article']],
                                ['idMemberShip', '=', $idMemberShip],
                                ['status', '=', 'Activo']
                            ])->update(['status' => 'Inactivo']);

                            $discount = new Descuentos;

                            $discount-> idMemberShip = $idMemberShip;
                            $discount->idUser = $idUser;
                            $discount->idProduct = $article['article'];
                            $discount->discount = $article['articleDiscount'];
                            $discount->reason = $article['articleReason'];
                            $discount-> finished_date = $article['articleCompletationDate'];

                            $discount->save();


                            $saveDiscountArticles = Arr::add($saveDiscountArticles, $article['article'], '{
                                "status":true,
                                "discount":'.$discount.'
                            }');


                        } catch (Throwable $e) {
                            report($e);

                            $saveDiscountArticles = Arr::add($saveDiscountArticles, $article['article'], '{
                                "status":false,
                                "discount":null
                            }');
                        }
                    }
                });


            return response()->json(
            [
                'status' => true,
                'DiscountGeneral' => $saveDiscountGeneral,
                'DiscountCategories' => $saveDiscountCategories,
                'DiscountSubCategories' => $saveDiscountSubCategories,
                'DiscountArticles' => $saveDiscountArticles

            ], 202);
        }
        catch (Throwable $e)
        {
            report($e);

            return response()->json(
                [
                    'status' => false,
                    'DiscountGeneral' => $saveDiscountGeneral,
                    'DiscountCategories' => $saveDiscountCategories,
                    'DiscountSubCategories' => $saveDiscountSubCategories,
                    'DiscountArticles' => $saveDiscountArticles

                ], 500);
        }
    }


    public function active_all_people()
    {
        try
        {
            return response()->json(
            [
                'status' => true,
                'discounts'=> Descuentos::where([
                    ['status', '=', 'Activo'],
                    ['idMemberShip', '=', null]
                ])->get()

            ], 202);
        }
        catch (Throwable $e)
        {
            report($e);

            return response()->json(
            [
                'status' => false,
                'discount'=> []

            ], 500);

        }

    }

}
