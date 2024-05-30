@extends('layouts.dashboard')

@section('title','Nuevo egreso')

@section('content')
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<div class="card col-9 offset-md-2">
    <div class="card-body">
        <h5 class="card-title">Registro de nuevo egreso</h5>
{{-- Formulario --}}
        <form action="{{url('/finanzas/guardar_egreso')}}" method="POST" autocomplete="off">
            <fieldset>
                @csrf
                <div class="row">
                    <div class="col-6">
                        <label for="">Tipo de egreso</label>
                        <select class="form-control" name="id_tipo_egreso" id="" required>
                            <option value="" selected disabled>Selecciona un tipo de egreso</option>
                            @foreach($tipo_egreso as $ti)
                            <option value="{{$ti -> id}}">{{$ti->tipo}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="concepto">Concepto de egreso</label>
                        <input type="text" class="form-control" name="concepto" id="concepto" aria-label="concepto">
                        <small>Opcional</small>
                    </div>
                    <div class="col-6">
                        <label for="numeoReferencias">Número de referencia</label>
                        <input type="text" class="form-control" name="numero_referencia" id="numero_referencia" aria-label="numero_referencia" pattern="[0-9]+" required>
                        <small>Número de referencia u orden de compra</small>
                    </div>
                    <div class="col-6">
                        <label for="montoEgreso">Monto del egreso</label>
                        <input type="text" class="form-control" name="monto" id="dinero" aria-label="nombreAlmacen" required>
                        <small>No use comas ni signos de pesos</small>
                    </div>
                </div>
                <br>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </fieldset>
        </form>
{{-- Termina el formulario --}}
    </div>
</div>
<br>
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
{{-- Notifica Errores --}}
<div class="col-9 offset-md-2">
    @if($errors->any())
    <div class="alert alert-danger" role="alert" fade show>
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <h4>{{$errors->first()}}</h4>
    </div>
    @endif
</div>
{{-- Inicia la tabla con el resumen de ingresos --}}
<div class=" card col-9 offset-md-2">
    <div class="card-body">
        <h5 class="card-title">Historial de egresos</h5>
        <fieldset>
            <div class="col-12">
                <table class="table" id="tabla_egresos">
                    <thead>
                        <tr>
                            <th>No. de operación</th>
                            <th>No. de referencia</th>
                            <th>Tipo de egreso</th>
                            <th>Concepto</th>
                            <th>Responsable</th>
                            <th>Fecha de transacción</th>
                            <th>Monto</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resumenEgresos as $re)
                        <tr>
                            <th scope="row" class="idEgreso_row">{{$re -> operacion}}</th>
                            <td>{{$re -> referencia}}</td>
                            <td>{{$re -> tipo}}</td>
                            <td>{{$re -> concepto}}</td>
                            <td>{{$re -> nombre}}</td>
                            <td>{{$re -> fecha}}</td>
                            <td>${{number_format($re -> monto,2)}}</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="opciones">
                                    <button href="" id="btn_editar" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#editmodal">Editar</button>
                                    <button href="" id="btn_borrar" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deletemodal">Eliminar</button>
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
<br>
{{-- Total de egresos --}}
<div class="card col-3 offset-md-8">
    <div class="card-body">
        <div class="col-12">
            <h6>Total de egresos: <strong>${{number_format($total,2)}}</strong></h6>
        </div>
    </div>
</div>
<br>
{{-- Modal para editar --}}
<div class="modal fade" id="editmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edición de registro</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
            </div>
            <div class="modal-body">
                <form action="/finanzas/actualizar_egreso" method="POST" autocomplete="off">
                    <fieldset>
                        @csrf
                        <input type="hidden" class="form-control" name="operacion"  id="operacion" aria-label="transaccion">
                        <div class="row">
                            <div class="col-12">
                                <label for="">Tipo de egreso</label>
                                <select class="form-control" name="id_tipo_egreso" id="id_tipo_egreso_modal" required>
                                    <option value="" selected disabled>Selecciona un tipo de ingreso</option>
                                    @foreach($tipo_egreso as $ti)
                                    <option value="{{$ti -> id}}">{{$ti->tipo}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="concepto">Concepto de egreso</label>
                                <input type="text" class="form-control" name="concepto" id="concepto_modal" aria-label="concepto">
                                <small>Opcional</small>
                            </div>
                            <div class="col-6">
                                <label for="numeoReferencias">Número de referencia</label>
                                <input type="text" class="form-control" name="numero_referencia" id="numero_referencia_modal" aria-label="numero_referencia" pattern="[0-9]+" required>
                                <small>Número de referencia u orden de compra</small>
                            </div>
                            <div class="col-6">
                                <label for="montoEgreso">Monto del egreso</label>
                                <input type="text" class="form-control" name="monto" id="dinero_modal" aria-label="dinero" required>
                                <small>No use comas ni signos de pesos</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar y cerrar</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- Modal para borrar --}}
<div class="modal fade" id="deletemodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atención usuario! Está a punto de borrar estos datos.</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
            </div>
            <div class="modal-body">
                <form action="/finanzas/borrar_egreso" method="POST" autocomplete="off">
                    <fieldset>
                        @csrf
                        <input type="hidden" class="form-control" name="operacion"  id="operacion_delete" aria-label="transaccion" readonly>
                        <input type="hidden" class="form-control" name="estatus"  id="estatus_delete" aria-label="transaccion" readonly>
                        <div class="row">
                            <div class="col-12">
                                <label for="">Tipo de egreso</label>
                                <select class="form-control" name="id_tipo_egreso" id="id_tipo_egreso_delete" required readonly>
                                    <option value="" disabled>Selecciona un tipo de ingreso</option>
                                    @foreach($tipo_egreso as $ti)
                                    <option value="{{$ti -> id}}" disabled>{{$ti->tipo}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="concepto">Concepto de egreso</label>
                                <input type="text" class="form-control" name="concepto" id="concepto_delete" aria-label="concepto" readonly>
                                <small>Opcional</small>
                            </div>
                            <div class="col-6">
                                <label for="numeoReferencias">Número de referencia</label>
                                <input type="text" class="form-control" name="numero_referencia" id="numero_referencia_delete" aria-label="numero_referencia" pattern="[0-9]+" required readonly>
                                <small>Número de referencia u orden de compra</small>
                            </div>
                            <div class="col-6">
                                <label for="montoEgreso">Monto del egreso</label>
                                <input type="text" class="form-control" name="monto" id="dinero_delete" aria-label="dinero" required readonly>
                                <small>No use comas ni signos de pesos</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Borrar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar y cerrar</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        //Le da formato y paginación a la tabla
        $('#tabla_egresos').DataTable();
    });
    //Lee - script que lla los datos de la fila que se quiere editar y los inyecta en el formulario ubicado en el modal
    $(document).ready(function(){
        $('#tabla_egresos').on('click','#btn_editar', function(){
            //Buscar - Busca el id del egreso
            var filaEgreso = $(this).closest("tr");
            var idEgreso = filaEgreso.find('.idEgreso_row').html();
            //console.log(idEgreso);
            //leer - buscamos los datos completos del id
            $.ajax({
                url:'/finanzas/buscarEgreso',
                type:'post',
                data:{
                    _token: $("input[name=_token]").val(),
                    idEgreso: idEgreso
                },
                success: function(response){
                    console.log(response);
                    $('#operacion').val(response.idEgreso);
                    $('#id_tipo_egreso_modal').val(response.idTipo);
                    $('#concepto_modal').val(response.concepto);
                    $('#numero_referencia_modal').val(response.referencia);
                    $('#dinero_modal').val(response.monto);
                },
                error: function(){
                    alert('Ocurrió un problema al consultar la base de datos');
                }
            });
        });
    //Modal para confirmar si el usuario realmente quiere borrar el registro
        $("#tabla_egresos").on('click','#btn_borrar', function(){
            //leer - Busca el id del egreso que se desea editar
            var filaEgreso = $(this).closest("tr");
            var idEgreso = filaEgreso.find(".idEgreso_row").html();
            console.log(idEgreso);
            //Leer - busca los datos completos del id relacionado con el egreso
            $.ajax({
                url:'/finanzas/buscarEgreso',
                type:'post',
                data:{
                    _token: $("input[name=_token]").val(),
                    idEgreso: idEgreso
                },
                success: function(response){
                    console.log(response);
                    $('#operacion_delete').val(response.idEgreso);
                    $('#id_tipo_egreso_delete').val(response.idTipo);
                    $('#concepto_delete').val(response.concepto);
                    $('#numero_referencia_delete').val(response.referencia);
                    $('#dinero_delete').val(response.monto);
                    $('#estatus_delete').val(response.estatus);
                },
                error: function(){
                    alert('Ocurrió un problema al consultar la base de datos');
                }
            });
        });
    });
</script>
@endsection
