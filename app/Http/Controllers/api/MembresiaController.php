<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use App\Models\Membresia;
use App\Models\PreciosMembresias;
use App\Models\Imagenes;
use App\Models\UsuarioMembresia;

use Illuminate\Database\Eloquent\SoftDeletes;

class MembresiaController extends Controller
{

    public function index(Request $request)
    {
        try
        {
            $membresia = Membresia::with([ 'preciosMembresia' => function($preciosMembresia) {

                $preciosMembresia->with('precioMembresia')-> where('status', '=', 'Activo');

            } ,'imagenes', 'discount.categorias', 'discount.subCategorias.padre','discount.subCategorias.image', 'discount.producto.images', 'discount.producto.categorias.padre'])-> get();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, $membresia);

            return response()->json($response);
        }
        catch (\Throwable $th)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, []);

            return response()->json($response);
        }
    }


    public function show($id)
    {
        try
        {
            $membresia = Membresia::with(['preciosMembresia' => function($preciosMembresia)
            {
                $preciosMembresia->with('precioMembresia')->where('status', '=', 'Activo');

            }, 'imagenes', 'discount.categorias', 'discount.subCategorias', 'discount.producto'])->find($id);

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, $membresia);

            return response()->json($response);


        }
        catch (\Throwable $th)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, []);

            return response()->json($response);
        }
    }

    public function storage(Request $request)
    {
        try {
            $membresia = Membresia::create($request-> only('name','description'));

            if ($membresia)
            {
                $preciosMembresias = new PreciosMembresias;

                $preciosMembresias-> idPrice = $request-> get('idPrice');
                $preciosMembresias-> idMemberShip = $membresia-> id;

                $preciosMembresias->save();

                $data = [
                    'idOrigin' => $membresia->id,
                    'type' => 'membresia'
                ];

                $imageDefault = $request->get('imageDefault');
                if(Str::contains($imageDefault, 'base64'))
                {
                    $url = app(\App\Http\Controllers\api\ImagenesController::class)->upload($imageDefault,'membresias');
                    app(\App\Http\Controllers\api\ImagenesController::class)->presSave($url, $data);
                }

                $imageAditional = $request->get('imageAditional');
                if(count($imageAditional))
                {
                    if(Str::contains($imageAditional[0], 'base64'))
                    {
                        app(\App\Http\Controllers\api\ImagenesController::class)->uploadsAndSave($imageAditional,$data,'membresias');
                    }
                }

                $benefits = $request->only('benefits');

                $newRequest = new Request;

                $newRequest->merge([
                    'idMemberShip' => $membresia->id,
                    'idUser'=> $benefits['benefits']['discount']['idUser'],
                    'idDiscountGeneral'=> $benefits['benefits']['discount']['idDiscountGeneral'],
                    'discountGeneral'=> $benefits['benefits']['discount']['discountGeneral'],
                    'generalReason'=> $benefits['benefits']['discount']['generalReason'],
                    'generalCompletationDate'=> $benefits['benefits']['discount']['generalCompletationDate'],
                    'discountCategories'=> $benefits['benefits']['discount']['discountCategories'],
                    'discountSubCategories'=> $benefits['benefits']['discount']['discountSubCategories'],
                    'discountArticles'=> $benefits['benefits']['discount']['discountArticles']
                ]);

                app(\App\Http\Controllers\api\DescuentosController::class)->create_membership($newRequest);

                $membresia-> preciosMembresia[0]->precioMembresia;
                $membresia-> imagenes;
                $membresia-> discount->each(function ($discount, $key) {

                    $discount-> categorias;
                    $discount-> subCategorias;
                    $discount-> producto;

                });

            }

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), true, $membresia);

            return response()->json($response);

        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(), false, []);

            return response()->json($response);
        }

    }

    public function update(Request $request, $id)
    {
        try {

            $membresia = Membresia::find($id);

            if (!$membresia)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), true, []);

                return response()->json($response);
            }

            $membresia-> name = $request->get('name');
            $membresia-> description = $request->get('description');
            $membresia-> updated_at = Carbon::now();

            $membresia-> save();

            // Actualización de precio
                $countPreciosMembresias =  PreciosMembresias::where([["idMemberShip","=",$membresia->id],["idPrice","=",$request-> get('idPrice')], ["status","=","Activo"]] )->get()->count();

                if ( !$countPreciosMembresias )
                {
                    $isUpdatePriceMembership = PreciosMembresias::where([ ["idMemberShip", "=", $membresia->id], ["status", "=", "Activo"] ])->update(['status' => "Inactivo", "updated_at" => Carbon::now()]);

                    if ($isUpdatePriceMembership) {

                        $preciosMembresias = new PreciosMembresias;

                        $preciosMembresias-> idPrice = $request-> get('idPrice');
                        $preciosMembresias-> idMemberShip = $membresia-> id;

                        $preciosMembresias->save();
                    }
                }
            //

            // Actualización de imagene por defecto
                $defaultImageBase64 = $request->get('imageDefault');
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
                                $url = app(\App\Http\Controllers\api\ImagenesController::class)->upload($defaultImageBase64,'membresias');

                                $newRequest = new Request();
                                $newRequest->merge(['uri_path' =>$url]);

                                app(\App\Http\Controllers\api\ImagenesController::class)->update($newRequest, $idImage->id);
                            }
                        break;
                    }
                });

            //

            // registro de imagenes adicionales
                Arr::first($request->get('imageAditional'), function ($imageBase64) use ( $id )
                {
                    if ( !Str::of($imageBase64)->exactly('current') )
                    {
                        $url = app(\App\Http\Controllers\api\ImagenesController::class)->upload($imageBase64,'membresias');
                        app(\App\Http\Controllers\api\ImagenesController::class)->presSave($url, ['idOrigin' => $id, 'type' => 'membresia']);
                    }
                });
            //


            $benefits = $request->only('benefits');

            $newRequest = new Request;

            $newRequest->merge([
                'idMemberShip' => $membresia->id,
                'idUser'=> $benefits['benefits']['discount']['idUser'],
                'idDiscountGeneral'=> $benefits['benefits']['discount']['idDiscountGeneral'],
                'discountGeneral'=> $benefits['benefits']['discount']['discountGeneral'],
                'generalReason'=> $benefits['benefits']['discount']['generalReason'],
                'generalCompletationDate'=> $benefits['benefits']['discount']['generalCompletationDate'],
                'discountCategories'=> $benefits['benefits']['discount']['discountCategories'],
                'discountSubCategories'=> $benefits['benefits']['discount']['discountSubCategories'],
                'discountArticles'=> $benefits['benefits']['discount']['discountArticles']
            ]);

            app(\App\Http\Controllers\api\DescuentosController::class)->create_membership($newRequest);

            $membresia-> preciosMembresia = PreciosMembresias::with('precioMembresia')->Where([["idMemberShip","=",$membresia->id], ["status","=","Activo"]])->get();

            $membresia-> imagenes;
            $membresia-> discount->each(function ($discount, $key) {

                $discount-> categorias;
                $discount-> subCategorias;
                $discount-> producto;

            });

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(), true, $membresia);

            return response()->json($response);

        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage(), false, []);

            return response()->json($response);
        }
    }


    public function destroy($id)
    {
        try {

            $membresia = Membresia::find($id);

            if (!$membresia)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), true, []);

                return response()->json($response);
            }

            $membresia-> delete();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getMessage(), true, $membresia);

            return response()->json($response);

        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getMessage(), false, []);

            return response()->json($response);
        }

    }

    public function membershipNumberControl($numberControl)
    {
        try
        {
            $UsuarioMembresia = UsuarioMembresia::with(['user.avatar','membresia.discount.categorias', 'membresia.discount.subCategorias.padre', 'membresia.discount.producto.images', 'membresia.discount.producto.categorias.padre'])->where([["numberControl","=", $numberControl], ["status","=", "Activo"]])->first();

            $response = $UsuarioMembresia ? new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, $UsuarioMembresia) :  new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), 2, $UsuarioMembresia);

            return response()->json($response);
        }
        catch (\Throwable $th)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, []);

            return response()->json($response);
        }
    }

}
