<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;
use Illuminate\Session\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;

use App\Models\Imagenes;
use App\Models\MultimediaEvidenciaSorteoPartido;
use Symfony\Component\HttpFoundation\Response;
use App\Models\MultimediaSorteo;


class ImagenesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Imagenes::where('status', true)->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($image)
    {
        return Imagenes::create($image);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Imagenes::where('id', $id)->where('status', true)->get();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $array = (Str::of($request->get("uri_path"))->explode('/'));

        $request->merge(['name' => $array[count($array) - 1]]);

        return Imagenes::where('id', $id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $image = Imagenes::where('id', $id)->first();
        Storage::delete($image->name);
        $image->delete();

        return [
            'status' => 'Eliminado'
        ];
    }


    public function uploadImage(Request $request)
    {
        try {
            $resultSet = null;
            $data = $request->all();
            $arrayResul = array();
            if ($request->has('image')) {
                if (!empty($data['image'])) {
                    $image_64 = $data['image'];
                    $url = $this->upload($image_64);
                    $resultSet = $this->presSave($url, $data);

                    return response()->json([
                        'status' => 'Subido',
                        'message' => 'Se ha subido la imagen',
                        'data'  => $resultSet
                    ]);
                } else {
                    $url = '/uploads/default-avatar.png';
                    $resultSet = $this->presSave($url, $data);
                    return response()->json([
                        'status' => 'Subido',
                        'message' => 'Se ha subido la imagen',
                        'data'  => $resultSet
                    ]);
                }
            } else if ($request->has('images')) {
                $images = $data['images'];
                foreach ($images as $image) {
                    $url = $this->upload($image);
                    $r = $this->presSave($url, $data);
                    array_push($arrayResul, $r);
                }
            }
        } catch (\Throwable  $e) {
            return [
                'status' => 'Error',
                'message' => $e->getMessage()
            ];
        }
    }


    public function presSave($url, $data, $name = null)
    {
        $nameArray = explode('/', $url);
        $image = [
            'rel_id' => $data['idOrigin'],
            'rel_type' =>  $data['type'],
            'uri_path' => $url,
            'name' => $name ? $name : $nameArray[count($nameArray) - 1]
        ];
        return $this->store($image);
    }

    public function updateAvatar($id, $url)
    {

        // $image = Imagenes::where([
        //     ['rel_id','=',$id],
        //     ['rel_type','=','usuario']
        // ])->update([
        //     'uri_path' => $url
        // ]);

        // ZurielDA
        $image = Imagenes::where('id', '=', $id)->update(['uri_path' => $url]);


        return $image;
    }

    public function upload($image_64, $disk = 'upload')
    {
        try {
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(11) . '.' . $extension;
            Storage::disk($disk)->put($imageName, base64_decode($image));
            $url = '/' . $disk . '/' . $imageName;
        } catch (\Exception $e) {
            throw  new \Exception('Ha ocurrido un error al cargar la imagen ' . $e->getMessage());
        }
        return $url;
    }

    public function replaceImage($image_64, $imageData, $disk = 'upload')
    {
        try {
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(11) . '.' . $extension;
            $url = '/' . $disk . '/' . $imageName;

            $img = Imagenes::where([
                ['rel_type', '=', $imageData['rel_type']],
                ['rel_id', '=', $imageData['rel_id']]
            ]);
            if ($img->exists()) {
                $oldData = $img->first();
                $resultSet = $img->update([
                    'uri_path' => $url
                ]);
                if ($resultSet) {
                    Storage::disk($disk)->put($imageName, base64_decode($image));
                    Storage::disk($disk)->delete($oldData->uri_path);
                }
            } else {
                $data = [
                    'type' => $imageData['rel_type'],
                    'idOrigin' => $imageData['rel_id']
                ];
                Storage::disk($disk)->put($imageName, base64_decode($image));
                $this->presSave($url, $data);
            }
        } catch (\Exception $e) {
            throw  new \Exception('Ha ocurrido un error al cargar la imagen ' . $e->getMessage());
        }
        return $url;
    }

    public  function  uploadsAndSave($images, $dataImage, $disk = 'upload')
    {
        foreach ($images as $image) {
            $url = $this->upload($image, $disk);
            $image  = $this->presSave($url, $dataImage);
        }
    }
    public function generateQrCode(string $code)
    {
    }

    public function uploadVideo(Request $request)
    {
        if ($request->file('video')->isValid()) {
            $file = $request->file('video');
            $videoName = Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move('videos', $videoName);
            $url = '/videos/' . $videoName;
            return [
                "url" => $url
            ];
        }
    }

    /**
     *
     * Zuriel DA
     *
     */


    public function getImageBlob($carpeta, $nombre)
    {
        try {
            // Local
            $path = public_path() . '\\' . $carpeta . '\\' . $nombre;

            // Servidor
            // $path = public_path().'/'.$carpeta.'/'.$nombre;

            if (!file_exists($path)) {
                abort(404);
            }

            $file = file_get_contents($path);

            $mime_type = mime_content_type($path);

            $base64 = base64_encode($file);

            $base64_with_prefix = 'data:' . $mime_type . ';base64,' . $base64;

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, [
                "base64" => $base64_with_prefix,
                "mimeType" => $mime_type
            ]);

            return response()->json($response);

        } catch (\Throwable $th) {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, null);

            return response()->json($response);
        }
    }

    public function uploadMedia($name ,$image_64 , $disk )
    {
        try
        {
            $name_image  = $name."_".Str::replace(' ','_',Str::replace(':', '', now()))."_".Str::random(10).".".Get_Extension_Image_Base64(Str::substr($image_64, 0, 100));

            if ($name_image)
            {
                Storage::disk($disk)->put($name_image, base64_decode(Get_Only_Base64($image_64)), 'public');

                return '/'. $disk .'/'. $name_image;
            }

            return null;
        }
        catch (\Exception $e)
        {
            throw  new \Exception('Ha ocurrido un error al cargar la imagen ' . $e->getMessage());

            return null;
        }
    }



    public function storeMultimediaRuffle(Request $request)
    {
        try {

            $name = $request->get('name');
            $type = $request->get('type');
            $id_raffle = $request->get('id_raffle');

            if (is_base64_image($name))
            {
                $newName = $type.'_'.$id_raffle;

                $name = $this-> uploadMedia($newName ,$name,'sorteos');

                $request->merge([ 'name' => $name ]);
            }

            if ($name)
            {
                $multimediaSorteoTemp =  MultimediaSorteo::create($request->only('id_raffle', 'name', 'type'));

                $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(), $multimediaSorteoTemp);

                return response()->json($response);
            }
            else
            {
                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), null);

                return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage() . $e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), null);

            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storeMultimediaEvidenceRuffleMathc(Request $request)
    {
        try
        {
            $name = $request->get('name');
            $type = $request->get('type');

            $id_evidence_raffle_match = $request->get('id_evidence_raffle_match');

            if (is_base64_image($name))
            {
                $newName = $type.'_'.$id_evidence_raffle_match;

                $name = $this-> uploadMedia($newName ,$name,'sorteos');

                $request->merge([ 'name' => $name ]);
            }

            if ($name)
            {
                $multimediaEvidenciaSorteoPartido =  MultimediaEvidenciaSorteoPartido::create($request->only('id_evidence_raffle_match', 'name', 'type'));

                $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(), $multimediaEvidenciaSorteoPartido);

                return response()->json($response);
            }
            else
            {
                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), null);

                return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage() . $e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), null);

            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     *
     *
     */
}
