<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Imagenes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Image;
use Illuminate\Support\Facades\File;

use function PHPUnit\Framework\isNull;

class ImagenesController extends Controller
{
    public function __construct(){
     
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Imagenes::where('status',true)->get();
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
    public function show($id){
        return Imagenes::where('id',$id)->where('status',true)->get();
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
        return Imagenes::where('id',$id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $image = Imagenes::where('id',$id)->first();
        echo json_encode($image);
        Storage::delete($image->name);
        $image->update([
            'status'=> false
        ]);
        return [
            'status' => 'Eliminado'
        ];
    }


    public function uploadImage(Request $request){
       try{
            $resultSet = null;
            $data = $request->all();
            $arrayResul = array();
            if($request->has('image')) {
                if(!is_null($data['image'])){
                    $image_64 = $data['image'];
                    $url = $this->upload($image_64);
                    $resultSet = $this->presSave($url,$data);

                    return response()->json([
                        'status' => 'Subido',
                        'message' => 'Se ha subido la imagen',
                        'data'  => $resultSet
                    ]);
                }else {
                    $url = env('APP_URL').'/storage/images/default-avatar.png';
                    $resultSet = $this->presSave($url,$data);
                    return response()->json([
                        'status' => 'Subido',
                        'message' => 'Se ha subido la imagen',
                        'data'  => $resultSet
                    ]);
                }
            }else if($request->has('images')) {
                $images = $data['images'];
                foreach($images as $image) {
                    $url = $this->upload($image);
                    $r = $this->presSave($url,$data);
                    array_push($arrayResul,$r);
                }
            }
       }catch(\Throwable  $e){
           return [
               'status' => 'Error',
               'message' => $e->getMessage()
           ];
       }
    }

    public function presSave($url, $data) {
         $image = [
            'rel_id' => $data['idOrigin'],
            'rel_type' =>  $data['type'],
            'uri_path' => $url
        ];
        return $resultSet = $this->store($image);
    }

    public function updateAvatar($id,$url) {
        $image = Imagenes::where([
            ['rel_id','=',$id],
            ['rel_type','=','usuario']
        ]);
        return $image->update([
            'uri_path' => $url
        ]);
    }
    public function upload($image_64){
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
        $replace = substr($image_64, 0, strpos($image_64, ',')+1);
        $image = str_replace($replace, '', $image_64);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10).'.'.$extension;
        Storage::disk('public')->put($imageName, base64_decode($image));
        $url = Storage::disk('public')->url($imageName);
        return $url;
    }

    public function uploadVideo(Request $request){
            if($request->file('video')->isValid()){
                $file = $request->file('video');
                $videoName = Str::random(10).'.'.$file->getClientOriginalExtension();
                $file->move('videos',$videoName);
                $url = env('APP_URL').'/videos/'. $videoName;
                return [
                    "url" => $url
                ];
            }
    }
}
