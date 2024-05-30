@extends('layouts.dashboard')

@section('title','Alumnos')

@section('content')
<input type="hidden" name="_token" value="{{ csrf_token() }}" />
<div class="card col-lg-9 offset-lg-2 ">
    <div class="card-body">
        <h5 class="card-title">Registro de alumnos</h5>
        <form action="{{url('academias/guardar_alumno')}}" method="POST" enctype="multipart/form-data" autocomplete="off" >
            <fieldset>
                @csrf
                <div class="row">
                    <div class="col-12">
                        <label for="curpAlumno">CURP<span class="obligatorio">*</span></label>
                        <input type="text" class="form-control" name="curp" id="curp" required>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-lg-3 col-sm-10">
                        <label for="nombres">Nombre(s)<span class="obligatorio">*</span></label>
                        <input type="text" class="form-control" name="nombreAlumno" id='nombreAlumno' required>
                    </div>
                    <div class="col-lg-3">
                        <label for="aPaterno">Primer apellido<span class="obligatorio">*</span></label>
                        <input type="text" class="form-control" name="appAlumno" id='appAlumno' required>
                    </div>
                    <div class="col-lg-3">
                        <label for="aPaterno">Segundo apellido<span class="obligatorio">*</span></label>
                        <input type="text" class="form-control" name="apmAlumno" id='apmAlumno' required>
                    </div>
                    <div class="col-lg-3">
                        <label for="nacimientoAlumno">Fecha de nacimiento<span class="obligatorio">*</span></label>
                        <input type="date" class="form-control" name="fechaNacimientoAlumno" id="fechaNacimientoAlumno" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <label for="genero">Género<span class="obligatorio">*</span></label>
                        <select class="form-control" name="generoAlumno" id="generoAlumno" required>
                            <option value="" selected disabled>seleccione un género</option>
                            <option value="1">femenino</option>
                            <option value="2">masculino</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <label for="actaNac">Acta de nacimiento<span class="obligatorio">*</span></label>
                        <input type="file" class="form-control-file" name="actaNac" id="actaNac" required>
                    </div>
                    <div class="col-lg-4">
                        <label for="actaNac">Certificado médico<span class="obligatorio">*</span></label>
                        <input type="file" class="form-control-file" name="certificadoMed" id="certificadoMed" required>
                    </div>
                    <div class="col-lg-4">
                        <label for="actaNac">Constancia de estudios<span class="obligatorio">*</span></label>
                        <input type="file" class="form-control-file" name="constanciaEst" id="constanciaEst" required>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-lg-4">
                        <label for="tutor">Nombre del tutor<span class="obligatorio">*</span></label>
                        <input type="text" class="form-control" name="tutorAlumno" id="tutorAlumno" required>
                    </div>
                    <div class="col-lg-4">
                        <label for="telefonoTutor">Teléfono del tutor<span class="obligatorio">*</span></label>
                        <input type="text" class="form-control" name="telefonoTutor" id="telefonoTutor" required>
                    </div>
                    <div class="col-lg-4">
                        <label for="ineTutor">INE del tutor<span class="obligatorio">*</span></label>
                        <input type="file" class="form-control-file" name="ineTutor" id="ineTutor" required>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-lg-6">
                        <label for="calle">Calle<span class="obligatorio">*</span></label>
                        <input type="text" class="form-control" name="calle" id="calle" required>
                    </div>
                    <div class="col-lg-3">
                        <label for="calle">Numero exterior<span class="obligatorio">*</span></label>
                        <input type="text" class="form-control" name="nExt" id="nExt" required>
                    </div>
                    <div class="col-lg-3">
                        <label for="calle">Numero interior</label>
                        <input type="text" class="form-control" name="nInt" id="nInt">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <label for="colonia">Colonia<span class="obligatorio">*</span></label>
                        <input type="text" class="form-control" name="colonia" id="calle" required>
                    </div>
                    <div class="col-lg-4">
                        <label for="postal">Código postal<span class="obligatorio">*</span></label>
                        <input type="text" class="form-control" name="cpostal" id="cpostal" required>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>

            </fieldset>

        </form>

    </div>
</div>
{{-- Notifica al usuario la ultima acción que acaba de realizar --}}
<div class="col-9 offset-md-2">
    @if (session('status'))
        <div class="alert alert-success" role="alert" fade show>
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ session('status') }}
        </div>
    @endif
</div>
<br>
{{-- Inicia la tabla con los alumnos  --}}
<div class="card col-lg-9 offset-lg-2">
    <div class="card-body">
        <h5 class="card-title">Alumnos</h5>
        <fieldset>
            <div class="col-12">
                <table class="table dt" id="tabla_alumnos" width="100%">
                    <thead>
                        <tr>
                            <th scope="col">No. Alumno</th>
                            <th scope="col">CURP</th>
                            <th scope="col">Apellido paterno</th>
                            <th scope="col">Apellido materno</th>
                            <th scope="col">opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumnosInfo as $ai)
                        <tr>
                            <th scope="row" class="idalumno_row">{{$ai -> idAlumno}}</th>
                            <th>{{$ai -> curp}}</th>
                            <td>{{$ai -> nombre}}</td>
                            <td>{{$ai -> papellido}}</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="opciones">
                                    <button type="button" data-all="{{json_encode($ai)}}" class="btn_detalle btn btn-info btn-sm" data-toggle="modal" data-target="#dataModal">Detalles</button>
                                    <button type="button" data-all="{{json_encode($ai)}}" class="btn_editar btn btn-secondary btn-sm" data-toggle="modal" data-target="#dataModal">Editar</button>
                                    <button type="button" data-all="{{json_encode($ai)}}" class="btn_borrar btn btn-danger btn-sm" data-toggle="modal" data-target="#deletemodal">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </fieldset>
    </div>
</div>

{{-- Modal para editar --}}
<div class="modal fade " id="dataModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Edición de alumno</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
            <form action="{{url('academias/actualizar_alumno')}}" method="POST" enctype="multipart/form-data" autocomplete="off" >
                <fieldset>
                    @csrf
                    <input type="hidden" class="form-control" name="idAlumno" id="idAlumno_data" readonly>
                     <div class="row">
                        <div class="col-12">
                            <label for="curp_data">CURP<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="curp" id="curp_data" required>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-3 col-sm-10">
                            <label for="nombres">Nombre(s)<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="nombreAlumno" id='nombreAlumno_data' required>
                        </div>
                        <div class="col-lg-3">
                            <label for="aPaterno">Primer apellido<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="appAlumno" id='appAlumno_data' required>
                        </div>
                        <div class="col-lg-3">
                            <label for="aPaterno">Segundo apellido<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="apmAlumno" id='apmAlumno_data' required>
                        </div>
                        <div class="col-lg-3">
                            <label for="nacimientoAlumno">Fecha de nacimiento<span class="obligatorio">*</span></label>
                            <input type="date" class="form-control" name="fechaNacimientoAlumno" id="fechaNacimientoAlumno_data" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <label for="genero">Género<span class="obligatorio">*</span></label>
                            <select class="form-control" name="generoAlumno" id="generoAlumno_data" required>
                                <option value="1">femenino</option>
                                <option value="2">masculino</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="urlActaNacimiento" class="control-label">Acta de nacimiento<span class="obligatorio">*</span></label>
                                <a href="#" target="_BLANK" class="form-control btn btn-secondary" id="urlActaNacimiento">Acta de nacimiento</a>
                                <input type="file" class="form-control-file" name="urlActaNacimiento_edit" id="urlActaNacimiento_edit">
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="certificadoMedico" class="control-label">Certificado médico<span class="obligatorio">*</span></label>
                                <a href="#" target="_BLANK" class="form-control btn btn-secondary" id="certificadoMedico">Certificado médico</a>
                                <input type="file" class="form-control-file" name="certificadoMedico_edit" id="certificadoMedico_edit">
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="constancia_url" class="control-label">Constancia de estudios<span class="obligatorio">*</span></label>
                                <a href="#" target="_BLANK" class="form-control btn btn-secondary" id="constancia_url">Constancia de estudios</a>
                                <input type="file" class="form-control-file" name="constancia_url_edit" id="constancia_url_edit">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-4">
                            <label for="tutor">Nombre del tutor<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="tutorAlumno" id="tutorAlumno_data" required>
                        </div>
                        <div class="col-lg-4">
                            <label for="telefonoTutor">Teléfono del tutor<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="telefonoTutor" id="telefonoTutor_data" required>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="ine_url" class="control-label">INE del tutor<span class="obligatorio">*</span></label>
                                <a href="#" target="_BLANK" class="form-control btn btn-secondary" id="ine_url">INE del tutor</a>
                                <input type="file" class="form-control-file" name="ine_url_edit" id="ine_url_edit" hidden>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-6">
                            <label for="calle">Calle<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="calle" id="calle_data" required>
                        </div>
                        <div class="col-lg-3">
                            <label for="calle">Numero exterior<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="nExt" id="calle_datanExt_data" required>
                        </div>
                        <div class="col-lg-3">
                            <label for="calle">Numero interior</label>
                            <input type="text" class="form-control" name="nInt" id="nInt_data">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <label for="colonia">Colonia<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="colonia" id="colonia_data" required>
                        </div>
                        <div class="col-lg-4">
                            <label for="postal">Código postal<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="cpostal" id="cpostal_data" required>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="submit" id="btnEditarFila" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </fieldset>
            </form>
            </div>
         </div>
    </div>
</div>

{{-- Modal, aviso de borrado lógico --}}
<div class="modal fade" id="deletemodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Atención! Está a punto de borrar esta información</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{url('academias/borrar_alumno')}}" method="post">
                    <fieldset>
                    @csrf
                    <input type="hidden" class="form-control" name="idAlumno" id="idAlumno_delete" readonly>
                    <div class="row">
                        <div class="col-12">
                            <label for="curp_data">CURP<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="curp" id="curp_delete" required readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-3 col-sm-10">
                            <label for="nombres">Nombre(s)<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="nombreAlumno" id='nombreAlumno_delete' required readonly>
                        </div>
                        <div class="col-lg-3">
                            <label for="aPaterno">Primer apellido<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="appAlumno" id='appAlumno_delete' required readonly>
                        </div>
                        <div class="col-lg-3">
                            <label for="aPaterno">Segundo apellido<span class="obligatorio">*</span></label>
                            <input type="text" class="form-control" name="apmAlumno" id='apmAlumno_delete' required readonly>
                        </div>
                        <div class="col-lg-3">
                            <label for="nacimientoAlumno">Fecha de nacimiento<span class="obligatorio">*</span></label>
                            <input type="date" class="form-control" name="fechaNacimientoAlumno" id="fechaNacimientoAlumno_delete" required readonly>
                        </div>
                    </div>
                    <hr>
                    <br>
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="submit" id="btnBorrarFila" class="btn btn-danger">Borrar</button>
                        </div>
                    </div>
                </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

  <script>
      $(document).on('click','.btn_borrar', function(){
        //detecta el botón borrar
        //var esBorrar = $(this).text() == 'Eliminar'
        var filaAlumno = $(this).closest("tr");
        var idAlumno = filaAlumno.find("idalumno_row").html();
        var response = $(this).data('all');
        console.log(response);
        
        $('#idAlumno_delete').val(response.idAlumno);
        $('#curp_delete').val(response.curp);
        $('#nombreAlumno_delete').val(response.nombre);
        $('#appAlumno_delete').val(response.papellido);
        $('#apmAlumno_delete').val(response.mapellido);
        $('#fechaNacimientoAlumno_delete').val(response.fechaNacimiento);

      });
      //Leer - llama los datos que se quieren editar del alumno
      $(document).on('click','.btn_detalle,.btn_editar', function(){
        //   Detectar boton puchado
        var esVer = $(this).text() == 'Detalles';
        //Cambia el nombre al modal según el botón puchado
        $('#dataModal .modal-title').html((esVer) ? 'Datos del alumno' : 'Edición del alumno');
        //busca el id del alumno
        var filaAlumno = $(this).closest("tr");
        var idAlumno = filaAlumno.find(".idalumno_row").html();
        //Trae todos los datos de la fila seleccionada
        var response = $(this).data('all');
        //console.log(response);
        //Le da formato al json con los inputs
        $arrIds = [ 'idAlumno_data','curp_data', 'nombreAlumno_data', 'appAlumno_data', 'apmAlumno_data', 'fechaNacimientoAlumno_data', 'generoAlumno_data', 'tutorAlumno_data', 'telefonoTutor_data', 'calle_data', 'calle_datanExt_data', 'nInt_data', 'colonia_data', 'cpostal_data'];
        //Inyecta los valores en los inputs del modal
        $('#idAlumno_data').val(response.idAlumno);
        $('#curp_data').val(response.curp);
        $('#nombreAlumno_data').val(response.nombre);
        $('#appAlumno_data').val(response.papellido);
        $('#apmAlumno_data').val(response.mapellido);
        $('#fechaNacimientoAlumno_data').val(response.fechaNacimiento);
        $('#generoAlumno_data').val(response.genero);
        $('#urlActaNacimiento').attr("href",path+response.urlActaNacimiento);
        $('#certificadoMedico').attr("href",path+response.certificadoMedico);
        $('#constancia_url').attr("href",path+response.constanciaEst);
        $('#ine_url').attr("href",path+response.ine_url);
        $('#tutorAlumno_data').val(response.nombreTutor);
        $('#telefonoTutor_data').val(response.telefonoTutor);
        $('#calle_data').val(response.calle);
        $('#calle_datanExt_data').val(response.nExt);
        $('#nInt_data').val(response.nInt);
        $('#colonia_data').val(response.colonia);
        $('#cpostal_data').val(response.cp);
        //Recorre el arreglo de inputs y los pone en disable según si el botón puchado es "detalles".
        $.each($arrIds,function(i,val){
            $('#'+val).attr('disabled',esVer);
        });
        //Pone en disable el botón guardar según si el botón puchado es "detalle".
        $('#btnEditarFila').attr('disabled',esVer);
        //Esconde los botones para ver documentos según si el botón puchado es "editar".
        $('#urlActaNacimiento').attr('hidden',!esVer);
        $('#certificadoMedico').attr('hidden',!esVer);
        $('#constancia_url').attr('hidden',!esVer);
        $('#ine_url').attr('hidden',!esVer);
        //Muestra los botos para cargar codumentos según si el botón puchado es "editar".
        $('#urlActaNacimiento_edit').attr('hidden',esVer);
        $('#certificadoMedico_edit').attr('hidden',esVer);
        $('#constancia_url_edit').attr('hidden',esVer);
        $('#ine_url_edit').attr('hidden',esVer);
      });
  </script>
@endsection
