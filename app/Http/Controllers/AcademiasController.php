<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


use DB;
//modelos
use App\Models\Alumnos as Alumno;

class AcademiasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function alumnosIndex()
    {
        $datos['alumnosInfo'] = DB::table('alumnos')
        ->where('estatus',1)
        ->get();
        return view('academias.alumnos.index', $datos);
    }

    //guarda la información del alumno
    public function guardarAlumno(Request $r)
    {
        DB::Begintransaction();
        try{
            $alumno = new Alumno();
            $alumno-> curp = $r->curp;
            $nombre = $alumno-> nombre = $r->nombreAlumno;
            $apellidop = $alumno-> papellido  = $r->appAlumno;
            $apellidom = $alumno-> mapellido = $r->apmAlumno;
            $nombrec = $nombre.$apellidop.$apellidom;
            $alumno-> fechaNacimiento = $r->fechaNacimientoAlumno;
            $alumno-> genero = $r->generoAlumno;
            $alumno-> nombreTutor = $r->tutorAlumno;
            $alumno-> telefonoTutor = $r->telefonoTutor;
            $alumno-> calle = $r->calle;
            $alumno-> nExt = $r->nExt;
            $alumno-> nInt = $r->nInt;
            $alumno-> colonia = $r->colonia;
            $alumno-> cp = $r->cpostal;
            $alumno-> created_at = Carbon::now();
            if($alumno->save()){
                //Acta de nacimiento
                $id = $alumno->idAlumno;
                $path = 'alumnos/'.$id;

                $nameActaNac = 'acta_de_nacimiento_'.$nombrec.'.pdf';
                $alumno->urlActaNacimiento = '/documentos/'.$path.'/'.$nameActaNac;
                //Certificado Médico
                $nameCertificadoMedico = 'certificado_médico_'.$nombrec.'.pdf';
                $alumno->certificadoMedico = '/documentos/'.$path.'/'.$nameCertificadoMedico;
                //Constancia de estudios
                $nameConstanciaEst = 'constancia_estudios_'.$nombrec.'.pdf';
                $alumno->constanciaEst = '/documentos/'.$path.'/'.$nameConstanciaEst;
                //INE del tutor
                $nameIneTutor = 'ine_tutor_'.$nombrec.'pdf';
                $alumno->ine_url = '/documentos/'.$path.'/'.$nameIneTutor; 

                $alumno->save();

                DB::commit();
                //guarda el acta de nacimiento
                $r->file('actaNac')->storeAs($path,$nameActaNac,'documentos');
                //guarda el certificado médico
                $r->file('certificadoMed')->storeAs($path,$nameCertificadoMedico,'documentos');
                //guarda la constancia de estudios
                $r->file('constanciaEst')->storeAs($path,$nameConstanciaEst,'documentos');
                //guarda el ine del tutor
                $r->file('ineTutor')->storeAs($path,$nameIneTutor,'documentos');
                return \Redirect::to('/academias/alumnos')->with('status','Alumno guardado con éxito');

            }else{
                DB::rollback();
                throw new Exception("Ocurrió un error al intentar registrar al alumno",400);
                // return view('/rollback');
                // return \Redirect::back()->withErrors(['Ocurrió un problema al intentar guardar la información']);
            }
        } catch(\Exception $e){
            DB::rollback();
            return response()->json(['errores'=>$e->getMessage()],$e->getCode());
            // return \Redirect::back()->withErrors(['Ocurrió un problema al intentar guardar la información']);
        }
    }

    /* 
    ! Leer alumno quedó en desuso por el momento 
    */   
    public function leerAlumno(Request $r)
    {
        $datos = DB::table('alumnos')
        ->where('idAlumno',$r->idAlumno)
        ->first();
        return response()->json($datos);
    }

    public function borrarAlumno(Request $r)
    {
        try{
            $alumno =  Alumno::find($r->idAlumno);
            $alumno -> estatus = 0;
            DB::Begintransaction();
            if($alumno->save()){
                DB::commit();
                return \Redirect::to('/academias/alumnos')->with('status','Alumno borrado con éxito');
            }else{
                DB::rollback();
                throw new Exception("Ocurrió un error al intentar actualizar el registro",400); 
            }

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['errores'=>$e->getMessage()],$e->getCode());
        }

    }

    public function actualizarAlumno(Request $r)
    {
        try{
            $alumno =  Alumno::find($r->idAlumno);
            $alumno-> curp = $r->curp;

            $nombre = $alumno-> nombre = $r->nombreAlumno;
            $apellidop = $alumno-> papellido  = $r->appAlumno;
            $apellidom = $alumno-> mapellido = $r->apmAlumno;
            $nombrec = $nombre.$apellidop.$apellidom;

            $alumno-> nombre = $r->nombreAlumno;
            $alumno-> papellido  = $r->appAlumno;
            $alumno-> mapellido = $r->apmAlumno;
            $alumno-> fechaNacimiento = $r->fechaNacimientoAlumno;
            $alumno-> genero = $r->generoAlumno;
            $alumno-> nombreTutor = $r->tutorAlumno;
            $alumno-> telefonoTutor = $r->telefonoTutor;
            $alumno-> calle = $r->calle;
            $alumno-> nExt = $r->nExt;
            $alumno-> nInt = $r->nInt;
            $alumno-> colonia = $r->colonia;
            $alumno-> cp = $r->cpostal;
            DB::Begintransaction();
            if($alumno->save()){
                DB::commit();
                $id = $alumno->idAlumno;
                $path = 'alumnos/'.$id;
                if($r->hasFile('urlActaNacimiento_edit')){
                    //Construye la ruta del acta de naciemiento
                    $nameActaNac = 'acta_de_nacimiento_'.$nombrec.'.pdf';
                    $alumno->urlActaNacimiento = '/documentos/'.$path.'/'.$nameActaNac;
                    /*Borramos el archivo actual*/ 
                    Storage::delete($path,$nameActaNac,'documentos');
                    //guarda el nuevo documento "Acta de nacimiento" en la carpeta 
                    $r->file('urlActaNacimiento_edit')->storeAs($path,$nameActaNac,'documentos');
                    DB::commit();
                }elseif($r->hasFile('certificadoMedico_edit')){
                    //Construye la ruta del certificado médico
                    $nameCertificadoMedico = 'certificado_médico_'.$nombrec.'.pdf';
                    $alumno->certificadoMedico = '/documentos/'.$path.'/'.$nameCertificadoMedico;
                    /*Borramos el archivo actual*/ 
                    Storage::delete($path,$nameCertificadoMedico,'documentos');
                    //Guarda el nuevo documento "Certificado Médico" en la misma carpeta
                    $r->file('certificadoMedico_edit')->storeAs($path,$nameCertificadoMedico,'documentos');
                    DB::commit();
                }elseif($r->hasFile('constancia_url_edit')){
                    //Construye la ruta de la constancia de estudios
                    $nameConstanciaEst = 'constancia_estudios_'.$nombrec.'.pdf';
                    $alumno->constanciaEst = '/documentos/'.$path.'/'.$nameConstanciaEst;
                    /*Borramos el archivo actual*/
                    Storage::delete($path,$nameConstanciaEst,'documentos');
                    //Guarda el nuevo documento "Constancia de estudios" en la misma carpeta
                    $r->file('constancia_url_edit')->storeAs($path,$nameConstanciaEst,'documentos');
                    DB::commit();
                }elseif($r->hasFile('ine_url_edit')){
                    //Construye la rua del INE del tutor
                    $nameIneTutor = 'ine_tutor_'.$nombrec.'pdf';
                    $alumno->ine_url = '/documentos/'.$path.'/'.$nameIneTutor;
                    /*Borramos el archivo actual*/
                    Storage::delete($path,$nameIneTutor,'documentos');
                    //Guarda el nuevo documento "INE del tutor"
                    $r->file('ine_url_edit')->storeAs($path,$nameIneTutor,'documentos');
                    DB::commit();
                }
                $alumno->save();
                return \Redirect::to('/academias/alumnos')->with('status','Alumno actualizado con éxito');
            }else{
                DB::rollback();
                throw new Exception("Ocurrió un error al intentar actualizar el registro",400);
                // return view('/rollback');
                // return \Redirect::back()->withErrors(['Ocurrió un problema al intentar guardar la información']);
            }
        } catch(\Exception $e){
            DB::rollback();
            return response()->json(['errores'=>$e->getMessage()],$e->getCode());
            // return \Redirect::back()->withErrors(['Ocurrió un problema al intentar guardar la información']);
        }

    }
}
