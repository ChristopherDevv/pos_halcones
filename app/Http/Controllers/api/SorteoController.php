<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Http\Request;
use App\Models\Sorteo;
use App\Models\SorteoPartido;
use App\Models\Partidos;
use App\Models\SorteoUsuario;
use PHPUnit\Util\Exception;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseStatusCodeSame;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SorteoController extends Controller
{
    public function index()
    {
        try
        {
            $sorteo = Sorteo::with(['multimedia', 'sorteoPartido.partido'])->where('status','=','Activo')->get();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$sorteo);

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try
        {
            $sorteo = Sorteo::with(['multimedia', 'sorteoPartido.partido'])->where([['status','=','Activo'], ['id', '=', $id]])->first();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$sorteo);

            return response()->json($response);
        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showUsersRaffle($id)
    {
        try
        {
            $sorteo = Sorteo::with(['sorteoUsuario' => function($sorteoUsuario)
            {

               $sorteoUsuario->select(['sorteo_usuario.id', 'users.id','nombre','apellidoP','apellidoM','correo']);

            }])->where([['status','=','Activo'], ['id', '=', $id]])->first();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode(),$sorteo);

            return response()->json($response);
        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showEvidenceUsers($id)
    {
        try
        {
            $sorteo = Sorteo::with(['multimedia', 'sorteoPartido' => function($sorteoPartido)
            {
                $sorteoPartido->with(['partido', 'evidenciaSorteoPartido' => function($evidenciaSorteoPartido)
                {
                    $evidenciaSorteoPartido-> with(['sorteoUsuario' =>function($sorteoUsuario)
                    {
                        $sorteoUsuario->with(['user' => function($user)
                        {
                            $user->select(['id','nombre', 'apellidoP', 'apellidoM','correo'])->with(['direcciones'=> function($direcciones){

                                $direcciones->select(['users_id','numTel'])->where('status', '=','2');

                            }]);
                        }]);

                    },'CodigoEvidenciaSorteoPartido','multimediaEvidenciaSorteoPartido']);

                }]);

            }])->where([['status','=','Activo'], ['id', '=', $id]])->first();

            if ($sorteo)
            {
                $sorteo->makeHidden('updated_at');

                foreach ($sorteo->sorteoPartido as $sorteoPartido)
                {
                    $sorteoPartido->makeHidden(['id','id_match','id_raffle','updated_at','created_at']);

                    $sorteoPartido->partido->makeHidden(['creation_date','id','id_match_season','updated_date']);

                    foreach ($sorteoPartido->evidenciaSorteoPartido as $evidenciaSorteoPartido)
                    {
                        $evidenciaSorteoPartido->makeHidden(['created_at','id','id_raffle_match','id_raffle_user','updated_at']);
                        $evidenciaSorteoPartido->sorteoUsuario->makeHidden(['id_user','updated_at','created_at','code']);
                        $evidenciaSorteoPartido->sorteoUsuario->user->makeHidden(['id']);
                    }
                }
            }

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$sorteo);

            return response()->json($response);
        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storeRaffle(Request $request)
    {
        try
        {
            $multimedia = $request->get('multimedia');
            $matchs = $request->get('matchs');

            $sorteoTemp =  Sorteo::create($request->only('type', 'name', 'description', 'rules', 'method_raffle', 'initial_date', 'finished_date'));

            if ($sorteoTemp && Arr::accessible($multimedia))
            {
                foreach ($multimedia as $key => $element)
                {
                    $requestTemp = new Request;

                    $requestTemp->merge(['id_raffle' => $sorteoTemp-> id , 'name' => $element['name'], 'type' => $element['type']]);

                    app(\App\Http\Controllers\api\ImagenesController::class)->storeMultimediaRuffle($requestTemp);
                }
            }

            if ($sorteoTemp && Arr::accessible($matchs))
            {
                foreach ($matchs as $key => $element)
                {
                    $requestTemp = new Request;

                    $requestTemp->merge([
                        'id_raffle' => $sorteoTemp-> id,
                        'id_match' => $element['id_match'],
                        'initial_date' => $element['initial_date'],
                        'finished_date' =>  $element['finished_date']
                    ]);

                    $this->storeRaffleMatch($requestTemp);
                }
            }

            $sorteoTemp->refresh();

            $sorteoTemp-> multimedia;

            foreach ($sorteoTemp-> sorteoPartido as $key => $element)
            {
                $element -> partido;
            }

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$sorteoTemp);

            return response()->json($response);
        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function storeRaffleMatch(Request $request)
    {
        try
        {
            $id_raffle = $request->get('id_raffle');
            $id_match = $request->get('id_match') ;

            $existSorteoUsuario= SorteoPartido::where([ ['id_raffle',"=", $id_raffle ], ['id_match',"=", $id_match] ])->first();

            if ( $existSorteoUsuario )
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_FOUND()->getCode(),null);

                return response()->json($response,Response::HTTP_NOT_FOUND);
            }

            $sorteoTemp = SorteoPartido::create($request->only('id_raffle', 'id_match', 'initial_date', 'finished_date'));

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$sorteoTemp);

            return response()->json($response);
        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    public function update($id, Request $request, Sorteo  $sorteo) {
        // try
        // {
        //     if ($id != $request->get('id'))
        //     {
        //         $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null );

        //         return response()->json($response,Response::HTTP_NOT_FOUND);
        //     }

        //     $sorteoTemp = $sorteo::find($id);

        //     $sorteoTemp-> price  = $request->get('price');

        //     $sorteoTemp-> save();

        //     $sorteoTemp-> refresh();

        //     $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(), $sorteoTemp );

        //     return response()->json($response);
        // }
        // catch (\Exception $e)
        // {
        //     $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),null);

        //     return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }

    public function destroy($id, Sorteo  $sorteo)
    {
        // try
        // {
        //     $sorteoTemp = $sorteo::find($id);

        //     if (!$sorteoTemp)
        //     {
        //         $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null);

        //         return response()->json($response);
        //     }

        //     $sorteoTemp-> delete();

        //     $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getCode(), $sorteoTemp);

        //     return response()->json($response);

        // }
        // catch (\Throwable $e)
        // {

        //     $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getMessage().$e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(), null);

        //     return response()->json($response);
        // }
    }
}
