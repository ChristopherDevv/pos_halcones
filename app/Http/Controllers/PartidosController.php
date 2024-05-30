<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partidos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartidosController extends Controller
{
   public function index() 
    {
        return Partidos::where('status',true)->with('images')->get();
    }

    public function indexToUpdate()
    {
        try {

            $partidos = $this->getAllPartidos();

            return view('partidos.actualizacionPartidos', [
                'partidos' => $partidos
            ]);

        } catch (\Exception $e) {
            return view('partidos.actualizacionPartidos', [
                'errorMessage' => $e->getMessage()
            ]);
        }
    }

    public function partidoToUpdate(Request $request)
    {
        try {
            $partidos = $this->getAllPartidos();
            $statuses = Partidos::STATUS;

            $partido = Partidos::where('id', $request->eventId)->with('images')->get()->map(function ($partido) {
                $partido->fecha = Carbon::parse($partido->fecha)->format('d-m-Y H:i');
                return $partido;
            });

            if($partido->isEmpty()){
                return view('partidos.actualizacionPartidos', [
                    'messageError' => 'No se ha encontrado el partido'
                ]);
            }
            return view('partidos.actualizacionPartidos', [
                'partidoToUpdate' => $partido,
                'partidos' => $partidos,
                'statuses' => $statuses,
                'messageSuccess' => 'Se ha encontrado el partido correctamente'
            ]);

        } catch (\Exception $e) {
            return view('partidos.actualizacionPartidos', [
                'partidos' => $partidos,
                'errorMessage' => $e->getMessage()
            ]);
        }

    }

    public function updatePartido(Request $request)
    {
        try {
            $partidos = $this->getAllPartidos();
            $statuses = Partidos::STATUS;
           /*  dd($request->file('imagePartido')); */
            $partidoToUpdate = Partidos::where('id', $request->idPartido)->first();

            if($request->statusPartido || $request->statusPartido === "0") {
                $partidoToUpdate->status = $request->statusPartido;
                $partidoToUpdate->save();
            }
            if($request->imagePartido){
                $image = $partidoToUpdate->images()->first();
                $url = $this->upload($request->imagePartido);
                if($image){
                    //eliminamos la imagen anterior
                    Storage::disk('upload')->delete($image->name);
                    $image->uri_path = $url['url'];
                    $image->name = $url['name'];
                    $image->save();
                } else {
                    $partidoToUpdate->images()->create([
                        'uri_path' => $url['url'],
                        'rel_id' => $partidoToUpdate->id,
                        'rel_type' => 'partidos',
                        'name' =>  $url['name']
                    ]);
                }
                
            }
            $partido = Partidos::where('id', $request->idPartido)->with('images')->get()->map(function ($partido) {
                $partido->fecha = Carbon::parse($partido->fecha)->format('d-m-Y H:i');
                return $partido;
            });

            return view('partidos.actualizacionPartidos', [
                'partidos' => $partidos,
                'partidoToUpdate' => $partido,
                'statuses' => $statuses,
                'messageSuccessSeconday' => 'Se ha actualizado el partido correctamente'
            ]);
          
        } catch(\Exception $e) {
            return view('partidos.actualizacionPartidos', [
                'partidos' => $partidos,
                'errorMessage' => $e->getMessage()
            ]);
        }
    }
    public function upload($imageFile, $disk = 'upload')
    {
        try {
            $extension = $imageFile->getClientOriginalExtension();
            $imageName = Str::random(11) . '.' . $extension;
            Storage::disk($disk)->put($imageName, file_get_contents($imageFile));
            $url = '/' . $disk . '/' . $imageName;
        } catch (\Exception $e) {
            throw  new \Exception('Ha ocurrido un error al cargar la imagen ' . $e->getMessage());
        }
        return ['url' => $url, 'name' => $imageName];
    }

    public function getAllPartidos()
    {
        $partidos = DB::table('partidos AS P')
            ->select('p.id', 'p.titulo', 'p.fecha')
            ->distinct()
            ->orderBy('p.fecha', 'desc')
            ->get();

        return $partidos;
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
    public function store(Request $request)
    {
        return Partidos::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        return Partidos::where('id',$id)->where('status',true)->get();
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
        return Partidos::where('id',$id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Partidos::where('id',$id)->update([
            'status'=> false
        ]);
    }
}
