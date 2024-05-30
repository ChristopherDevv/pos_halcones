<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Models\Codigos;
use App\Models\User;
use App\Models\Productos;
use App\Models\CodigosDescuento;
use App\Models\OrdenesCodigos;
use App\Mail\CodigoDescuentoProductoEnviado;
use Illuminate\Support\Facades\Mail;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;

class CodigosDescuentoController extends Controller
{
    public function storage(Request $request)
    {
        if (Str::of($request->get('amountType'))->exactly('totalUser'))
        {
            // Obtención y filtrado de usuarios
                $query = User::Select('id','nombre', 'apellidoP', 'apellidoM', 'sexo', 'correo', 'creation_date');

                Arr::first($request->get('genres'), function ($conditional, $key)  use (&$query)
                {
                    $query->orWhere('sexo','=',$conditional);
                });

                $users = $query->get();
            //

            if ($users->count())
            {
                // Actualización de total de usos de un codigo de acuerdo a la cantidad de usuarios
                    $request->merge(['numberUses' => $users->count()]);
                //

                //  Información base del descuento
                    $codigoDescuento = CodigosDescuento::create($request->only(['idProduct', 'numberUses','uniqueCode','discount','finished_at']));
                //

                if ($codigoDescuento)
                {
                    // Si no se ingresa codigo se asigna automaticamente con un trigger en la base de datos
                    $code = $request->get('code');

                    $product = Productos::with('categorias.padre','images')->find($codigoDescuento-> idProduct);

                    if($product)
                    {
                        // Creación de cada codigo de descuento por usuario
                        // Se guarda la lista de usuarios a los que no se le generaron los códigos
                        $userErrorGenerateCode = collect([]);
                        $userGenerateCode = collect([]);

                            $users->each(function ($user, $key) use ($codigoDescuento, $code, $product, &$userErrorGenerateCode, &$userGenerateCode)
                            {
                                $codigo = new Codigos;

                                $codigo-> idUser = $user->id;
                                $codigo-> idCodeDiscount = $codigoDescuento->id;
                                $codigo-> code = $code;

                                $isSave = $codigo-> save();

                                if ($isSave)
                                {
                                    $codigo-> refresh();

                                    $informationCode = [
                                        'category' => $product-> categorias -> padre -> title,
                                        'subcategory' => $product-> categorias -> title,
                                        'article' => $product-> title,
                                        'path_image'=> $product -> images[0]['uri_path'],
                                        'discount' => $codigoDescuento-> discount,
                                        'code' => $codigo-> code,
                                        'finished_at' => $codigoDescuento-> finished_at
                                    ];

                                    Mail::to($user-> correo)->send(new CodigoDescuentoProductoEnviado( $informationCode ));

                                    $userGenerateCode->push([
                                        'email' => $user-> correo,
                                        'code' => $codigo-> code
                                    ]);
                                }
                                else
                                {
                                    $userErrorGenerateCode->push([
                                        'informationCode' =>[
                                            'idUser' => $user->id,
                                            'idCodeDiscount' => $codigoDescuento->id,
                                            'code' =>  $code
                                        ]
                                    ]);
                                }

                            });

                            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), true, [
                                'informationCodeDiscount' => [
                                    'idProduct' => $codigoDescuento-> idProduct,
                                    'numberUses' => $codigoDescuento-> numberUses,
                                    'uniqueCode' => $codigoDescuento-> uniqueCode,
                                    'discount' => $codigoDescuento-> discount,
                                    'finished_at' => $codigoDescuento-> finished_at
                                ],
                                'informationProduct' => [
                                    'category' => $product-> categorias -> padre -> title,
                                    'subcategory' => $product-> categorias -> title,
                                    'article' => $product-> title,
                                    'path_image'=> $product -> images[0]['uri_path'],
                                ],
                                'codeUserGenerate' => $userGenerateCode,
                                'userErrorGenerateCode' => $userErrorGenerateCode
                            ]);

                            return response()->json($response);
                        //
                    }
                    else
                    {
                        // Retornar mensaje de que no se encontro el producto sobre el que se hace el descuento
                    }

                }
                else
                {

                    // Retornar mensaje de que no se puedo generar el descuento

                }
            }
            else
            {
                // Retornar mensaje de que no se encontraron usuarios
            }

        }
        else
        {

            // Falta hacer la generación de codigos para los usuarios xD
            //
            //
            //
            //
            //

            $userErrorGenerateCode = collect([]);
            $userGenerateCode = collect([]);
            $generateCodeWithoutUser = collect([]);

            if (count($request->get('users'))) {

            }
        }
    }

    public function verifyCode( $code ){

        try
        {

            $existCode =  Codigos::where('code','=',$code)->get()->count();

            return response()->json(
            [
                'status' => true,
                'existCode'=> $existCode ? true : false

            ], 202);
        }
        catch (Throwable $e)
        {
            report($e);

            return response()->json(
            [
                'status' => false,
                'existCode'=> null

            ], 500);

        }

    }

}
