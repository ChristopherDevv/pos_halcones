@extends('layouts.dashboard')

@section('title','Nuevo tipo de ingreso')

@section('content')
<br>
{{-- Notifica al usuario la ultima acción que acaba de realizar --}}
<div class="col-9 offset-md-1">
    @if (session('status'))
        <div class="alert alert-success" role="alert" fade show>
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ session('status') }}
        </div>
    @endif
</div>
<br>
<div class="row">
    <div class="card col-5  ">
        <div class="card-body">
            <h5 class="card-title">Registro de nuevo tipo de ingreso</h5>
            {{-- Formulario --}}
            <form action="{{url('/finanzas/guardarTipoIngreso')}}" method="POST" autocomplete="off">
                <fieldset>
                    @csrf
                    <div class="row">
                        <div class="col-12 ">
                            <label for="">Tipo de ingreso</label>
                            <input type="text" class="form-control" name="tipo_ingreso"  id="tipo_ingreso" aria-label="tipo_ingreso">
                        </div>
                    </div>
                    <br>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>

    <div class="card col-5 offset-md-1">
        <div class="card-body">
            <h5 class="card-title">Tipos de ingreso registrados</h5>
            {{-- Tabla --}}
            <fieldset>
                <div class="col-12">
                    <table class="table" id="tabla_tipos_ingreso">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tipo de ingreso</th>
                                <th>Estatus</th>
                                <th>Usuario que lo registró</th>
                                <th>Fecha de registro</th>
                                <th>opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tipos_ingresos as $ti)
                            <tr>
                                <th scope="row" id="tipoIngreso_row">{{$ti->id}}</th>
                                <th>{{$ti->tipo}}</th>
                                @if($ti->estatus == 1)<th>Activo</th>@else<th>Inactivo</th>@endif
                                <th>{{$ti->usuario}}</th>
                                <th>{{$ti->created_at}}</th>
                                <th>faltan las opciones</th>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        //Le da formato a la tabla
        $('#tabla_tipos_ingreso').DataTable();
    });
</script>




@endsection
