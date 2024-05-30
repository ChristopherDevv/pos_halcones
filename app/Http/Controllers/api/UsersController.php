<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Productos;
use App\Models\UsuarioMembresia;
use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use App\Models\Interfaces\ErroresExceptionEnum;
use App\Models\Sorteo;
use App\Models\SorteoUsuario;
use PHPUnit\Util\Exception;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseStatusCodeSame;
use Symfony\Component\HttpFoundation\Response;


class UsersController extends Controller
{
    public function showUers($id){
        return User::where('id',$id)->where('status',true)->with(['roles','avatar','usuarioMembresias.membresia.discount', 'sorteos' => function($sorteos)
        {
            $sorteos-> with(['evidenciaSorteoPartido' => function($evidenciaSorteoPartido)
            {
                $evidenciaSorteoPartido->with(['sorteoPartido.sorteo','sorteoPartido.partido', 'codigoEvidenciaSorteoPartido', 'multimediaEvidenciaSorteoPartido']);

            },'sorteo.sorteoPartido']);

        }])->first();
    }

    public function update(Request $request, $id)
    {
        try {
           $requestData = $request->all();

           $user = User::where('id',$id)->update($request->except('idAvatar' ,'avatar', 'idAvatarMembresia', 'avatarMembresia'));

        //     if($user > 0) {
        //         $avatar = $requestData['avatar'];
        //         if (!is_null($avatar)) {
        //             $url = app(\App\Http\Controllers\api\ImagenesController::class)->upload($avatar);
        //             $updated = app(\App\Http\Controllers\api\ImagenesController::class)->updateAvatar($id, $url);
        //         }
        //         $user = User::where('id',$id)->with(['roles','avatar'])->first();
        //         return response()->json($user);
        //    }

            if($user > 0)
            {
                if( $request->has('avatar') )
                {
                    $avatar = $requestData['avatar'];

                    if (!is_null($avatar))
                    {
                        $url = app(\App\Http\Controllers\api\ImagenesController::class)-> upload( $avatar );

                        $updated = app(\App\Http\Controllers\api\ImagenesController::class)-> updateAvatar( $requestData['idAvatar'] , $url );
                    }
                }

                // ZurielDA
                // Se agrega para añadir o actualizar la imagen de membresia.
                    if( $request->has('avatarMembresia') )
                    {
                        $avatarMembresia = $requestData['avatarMembresia'];
                        $idAvatarMembresia = $requestData['idAvatarMembresia'];

                        if (is_null($idAvatarMembresia))
                        {
                                $newRequest = new Request;
                                $newRequest-> merge(['image' => $avatarMembresia, 'idOrigin'=> $id,  'type' => 'usuario']);

                                app(\App\Http\Controllers\api\ImagenesController::class)->uploadImage($newRequest);
                        }
                        else
                        {
                            $url = app(\App\Http\Controllers\api\ImagenesController::class)-> upload( $avatarMembresia );
                            $updated = app(\App\Http\Controllers\api\ImagenesController::class)-> updateAvatar( $idAvatarMembresia , $url );
                        }

                    }
                //

                $user = User::where('id',$id)->with(['roles','avatar'])->first();

                return response()->json($user);
            }

        }catch(\Throwable $e){
            $response = new DataResponse($e->getMessage(),'Error',$e);
            return response()->json($response);
        }
    }



    /**
     *
     * ZurielDA
     *
     */

    public function showUsersClients(Request $request)
    {
        try
        {
            if ($request->has('conditionalWhere'))
            {
                $whereArray = [];

                $conditionalWhere  = Arr::accessible($request-> get('conditionalWhere')) ? $request-> get('conditionalWhere') :  json_decode($request-> get('conditionalWhere'), true);

                Arr::first($conditionalWhere, function ($conditional, $key)  use (&$whereArray)
                {
                    $whereArray = Arr::prepend($whereArray, [$conditional['key'],$conditional['operator'],$conditional['value']]);
                });

                $user = User::Select('nombre', 'apellidoP', 'apellidoM', 'sexo', 'correo', 'creation_date')-> Where($whereArray)->get();

                $response =  new DataResponse('Se ha obtenido la información de los usuarios','PROCESS_SUCESS',$user);

                return response()->json($response,200);

            }

        } catch (\Throwable $e)
        {
            $response = new DataResponse($e->getMessage(),'Error',$e);

            return response()->json($response);
        }
    }

    public function showUsersGenres(Request $request)
    {
        try
        {
            $genres = User::Select('sexo')->groupBy('sexo')->get();

            $response =  new DataResponse('Se ha obtenido la información de los usuarios','PROCESS_SUCESS', $genres);

            return response()->json($response,200);

        } catch (\Throwable $e)
        {
            $response = new DataResponse($e->getMessage(),'Error',$e);

            return response()->json($response);
        }
    }

    public function userMembership( Request $request )
    {
        try {

            $user = User::where('correo',"=", $request-> get('correo'))->get();

            $userMembership = new UsuarioMembresia;

            $userMembership-> idUser = $user[0]-> id;
            $userMembership-> idMemberShip = $request-> get('idMemberShip');
            $userMembership-> finished_at = Carbon::now()->addYears(1);

            $userMembership-> save();

            $user = User::with('usuarioMembresias.membresia.discount')->find($user[0]->id);

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), true, $user->usuarioMembresias);

            return response()->json($response);

        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, []);

            return response()->json($response);
        }
    }


    // Pendiente
    public function storageRaffleUser(Request $request)
    {
        try
        {
            $id_raffle = $request->get('id_raffle');
            $id_user = $request->get('id_user');

            $existSorteoUsuario = SorteoUsuario::where([ ['id_raffle',"=", $id_raffle ], ['id_user',"=", $id_user] ])->first();

            if ( $existSorteoUsuario )
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_FOUND()->getCode(), "Ya se encuentra registrado en el sorteo");

                return response()->json($response,Response::HTTP_NOT_FOUND);
            }

            // Validar entrada para el sorteo
            // if ( $request->get('code') )
            // {
            // }

            $sorteoUsuario =  SorteoUsuario::create($request->only('id_raffle', 'id_user', 'code'));

            $sorteoUsuario-> refresh();

            foreach ($sorteoUsuario-> sorteo -> sorteoPartido as $key => $element)
            {
                $element -> partido;
            }

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$sorteoUsuario);

            return response()->json($response);
        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), $e->getMessage());

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function showRaffleUser($id_user)
    {
        try
        {
            $sorteoUsuario = SorteoUsuario::with(['evidenciaSorteoPartido' => function($evidenciaSorteoPartido)
            {
                $evidenciaSorteoPartido->with(['sorteoPartido.sorteo','sorteoPartido.partido', 'codigoEvidenciaSorteoPartido', 'multimediaEvidenciaSorteoPartido']);

            },'sorteo.sorteoPartido'])->where('id_user',"=", $id_user)->get();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(), $sorteoUsuario );

            return response()->json($response);
        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),$e->getMessage());

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     *
     *
     *
     */

}
