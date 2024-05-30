@extends('layouts.dashboard')

@section('title','Nuevo ingreso')

@section('content')
<input type="hidden" name="_token" value="{{ csrf_token() }}" />
<div class="card col-9 offset-md-2">
    <div class="card-body">
        <h5 class="card-title">Registro de nuevo ingreso</h5>
{{-- Formulario --}}
        <form action="{{url('/finanzas/guardar_ingreso')}}" method="POST" autocomplete="off">
            <fieldset>
            @csrf
            <div class="row">
                <div class="col-6">
                    <label for="">Tipo de ingreso</label>
                    <select class="form-control" name="id_tipo_ingreso" aria-label="tipo_ingreso" required>
                        <option value="" selected disabled>Selecciona un tipo de ingreso</option>
                        @foreach($tipo_ingreso as $ti)
                        <option value="{{$ti->id}}">{{$ti->tipo}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 ">
                    <label for="">Concepto de ingreso</label>
                    <input type="text" class="form-control" name="concepto"  id="concepto" aria-label="concepto">
                    <small>Opcional</small>
                </div>
                <div class="col-6 ">
                    <label for="">Número de referencia</label>
                    <input type="text" class="form-control" name="numero_referencia"  id="numero_referencia" aria-label="numero_referencia" pattern="[0-9]+" required>
                    <small>Número de referencia de la transferencia bancaria.</small>
                </div>
                <div class="col-6 ">
                    <label for="">Monto del ingreso</label>
                    <input type="text" class="form-control" name="monto"  id="dinero" aria-label="nombreAlmacen" required>
                    <small>No coloque comas ni signos de pesos</small>
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
{{-- Inicia la tabla con el resumen de ingresos --}}
<div class="card col-9 offset-md-2">
    <div class="card-body">
        <h5 class="card-title">Historial de ingresos</h5>
        <fieldset>
            <div class="col-12">
                <table class="table" id="tabla_ingresos">
                    <thead>
                        <tr>
                            <th scope="col">No.Transacción</th>
                            <th scope="col">No. de referencia</th>
                            <th scope="col">Tipo de ingresos</th>
                            <th scope="col">Concepto</th>
                            <th scope="col">Responsable</th>
                            <th scope="col">Fecha de transacción</th>
                            <th scope="col">Monto</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resumenIngreso as $ri)
                        <tr>
                            <th scope="row" class="idIngreso_row">{{$ri -> transaccion}}</th>
                            <td>{{$ri -> referencia}}</td>
                            <td>{{$ri -> tipo}}</td>
                            <td>{{$ri -> concepto}}</td>
                            <td>{{$ri -> nombre}}</td>
                            <td>{{$ri -> fecha}}</td>
                            <td>${{number_format($ri -> monto,2)}}</td>
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
{{-- Total de ingresos --}}
<div class="card col-3 offset-md-8">
    <div class="card-body">
        <div class="col-12">
            <h6>Total de ingresos: <strong>${{number_format($total)}}</strong></h6>
        </div>
    </div>
</div>
<br>
{{-- Modal para editar --}}
<div class="modal fade" id="editmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Edición de registro</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form action="{{url('/finanzas/actualizar_ingreso')}}" method="POST" autocomplete="off">
                <fieldset>
                @csrf
                <input type="hidden" class="form-control" name="transaccion"  id="transaccion" aria-label="transaccion">
                <div class="row">
                    <div class="col-12">
                        <label for="">Tipo de ingreso</label>
                        <select class="form-control" name="id_tipo_ingreso" id="id_tipo_ingreso_modal" aria-label="tipo_ingreso" required>
                            <option value=""  disabled>Selecciona un tipo de ingreso</option>
                            @foreach($tipo_ingreso as $ti)
                            <option  value="{{$ti->id}}">{{$ti->tipo}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="">Concepto</label>
                        <input type="text" class="form-control" name="concepto"  id="concepto_modal" aria-label="concepto">
                        <small>Opcional</small>
                    </div>
                    <div class="col-6 ">
                        <label for="">Número de referencia</label>
                        <input type="text" class="form-control" name="numero_referencia"  id="numero_referencia_modal" aria-label="numero_referencia" required>
                        <small>Número de referencia de la transferencia bancaria.</small>
                    </div>
                    <div class="col-6 ">
                        <label for="">Monto del ingreso</label>
                        <input type="text" class="form-control" name="monto"  id="dinero_modal" aria-label="nombreAlmacen" required>
                        <small>No coloque comas ni signos de pesos</small>
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

  {{-- Modal para borrar registros --}}
  <div class="modal fade" id="deletemodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Atención usuario! Está a punto de borrar estos datos.</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form action="{{url('/finanzas/borrarIngreso')}}" method="POST" >
                <fieldset>
                @csrf
                <input type="hidden" class="form-control" name="transaccion"  id="transaccion_delete" aria-label="transaccion" readonly>
                <input type="hidden" class="form-control" name="estatus"  id="estatus_delete" aria-label="transaccion" readonly>
                <div class="row">
                    <div class="col-12">
                        <label for="">Tipo de ingreso</label>
                        <select class="form-control" name="id_tipo_ingreso" id="id_tipo_ingreso_modal_delete" aria-label="tipo_ingreso" required readonly>
                            <option value=""  disabled>Selecciona un tipo de ingreso</option>
                            @foreach($tipo_ingreso as $ti)
                            <option  value="{{$ti->id}}" disabled>{{$ti->tipo}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="">Concepto</label>
                        <input type="text" class="form-control" name="concepto"  id="concepto_delete" aria-label="concepto" required readonly>
                        <small>Opcional</small>
                    </div>
                    <div class="col-6 ">
                        <label for="">Número de referencia</label>
                        <input type="text" class="form-control" name="numero_referencia"  id="numero_referencia_modal_delete" aria-label="numero_referencia" required readonly>
                        <small>Número de referencia de la transferencia bancaria.</small>
                    </div>
                    <div class="col-6 ">
                        <label for="">Ingreso</label>
                        <input type="text" class="form-control" name="monto"  id="dinero_modal_delete" aria-label="nombreAlmacen" required readonly>
                        <small>No coloque comas ni signos de pesos</small>
                    </div>
                </div>
                    <br>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Borrar</button>
                    </div>
                </fieldset>
            </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar y cerrar</button>
        </div>
      </div>
    </div>
  </div>

<script>
    //plugin - Script del plugin data tables y otras validaciones
    $(document).ready(function(){
        //Le da formato y paginación a la tabla
        $('#tabla_ingresos').DataTable();
        //Otras validaciones
        //Impide la entrada de números en el campo "Número de referencia"
        $("#numero_referencia").on('input', function(event){
            $(this).val($(this).val().replace(/[^0-9]/g,''));
        });
        //Impide la entrada de letras en el campo "Ingreso"
        $("#dinero").on('input', function(event){
            $(this).val($(this).val().replace(/[^0-9]/g,''));
        });
        //Impide la entrada de letras en el campo "Ingreso" en el modal
        $("#dinero_modal").on('input', function(event){
            $(this).val($(this).val().replace(/[^0-9]/g,''));
        });
        //Impide la entrada de letras en el campo "Número de referencia" en el modal
        $("#numero_referencia_modal").on('input', function(event){
            $(this).val($(this).val().replace(/[^0-9]/g,''));
        });
    });
    //leer - script que llama los datos de la fila que se quiere editar
    //y los inyecta en el formulario ubicado en el modal
    $(document).ready(function(){
        $("#tabla_ingresos").on('click','#btn_editar', function(){
            //buscar - busca el id del ingreso
            var filaIngreso = $(this).closest("tr");
            var idIngreso = filaIngreso.find(".idIngreso_row").html();
            //console.log(idIngreso);
            //leer - pedimos los datos completos del id
            $.ajax({
                url: '/finanzas/buscarIngreso',
                type:'post',
                data:{
                    _token: $("input[name=_token]").val(),
                    idIngreso: idIngreso
                },
                success: function(response){
                    //alert('Todo salió bien');
                    //console.log(response);
                    $('#transaccion').val(response.transaccion);
                    $('#id_tipo_ingreso_modal').val(response.idTipo);
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
        $("#tabla_ingresos").on('click','#btn_borrar', function(){
            //leer - Busca el id del ingreso que se desea borrar
            var filaIngreso = $(this).closest("tr");
            var idIngreso = filaIngreso.find(".idIngreso_row").html();
            console.log(idIngreso);
            //Obtenemos los datos relacionados con el ingreso
            $.ajax({
                url: '/finanzas/buscarIngreso',
                type:'post',
                data:{
                    _token: $("input[name=_token]").val(),
                    idIngreso: idIngreso
                },
                success: function(response){
                    console.log(response);
                    $('#transaccion_delete').val(response.transaccion);
                    $('#id_tipo_ingreso_modal_delete').val(response.idTipo);
                    $('#concepto_delete').val(response.concepto);
                    $('#numero_referencia_modal_delete').val(response.referencia);
                    $('#dinero_modal_delete').val(response.monto);
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
