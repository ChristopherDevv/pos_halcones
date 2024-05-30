@extends('layouts.dashboard')

@section('title','Corte de Caja')

@section('content')

<!-- Content Row -->
<form action="corte_carga" method="post">
    @csrf
    <div class="row">
        <div class="col-lg-4">
            <label for="nombreTaquillero">Taquillero </label>
            <select class="form-control" name="nombreTaquillero" id="nombreTaquillero" required>
                <option value="" selected disabled>seleccione un taquillero</option>
                @if(!empty($usuarios))
                    @foreach($usuarios as $usuario)
                        <option value="{{$usuario->id}}">{{$usuario->nombre}}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="col-lg-2">
            <br>
            <button style="padding-top: 10%;" type="submit" class="btn btn-primary">Cargar taquillero</button>
        </div>
    </div><br>
</form>
<form action="corte_resultado" method="post">
    @csrf
    <div class="row">
        @if(!empty($fechas))
                <div class="col-lg-4">
                    <label for="dato_taquillero">Taquillero </label>
                    <select class="form-control" name="dato_taquillero" id="dato_taquillero" required>
                        @if(!empty($user))
                            <option selected value="{{$idUser}}">{{$user}}</option>
                        @endif
                    </select>
                </div>
                <div class="col-lg-4">
                    <label for="fecha">Fecha </label>
                    <select class="form-control" name="fecha" id="fecha" required>
                        <option value="" selected disabled>seleccione una fecha</option>
                        @if(!empty($fechas))
                            @foreach($fechas as $objeto)
                                <option value="{{$objeto['fecha_compra']}}">{{$objeto['fecha_compra']}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-lg-4">
                    <label for="jornada">Jornada </label>
                    <select class="form-control" name="jornada" id="jornada" required>
                        <option value="" selected disabled>seleccione una jornada</option>
                        @if(!empty($jornadas))
                            @foreach($jornadas as $objeto2)
                                <option value="{{$objeto2['titulo']}}">{{$objeto2['titulo']}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-lg-2">
                    <br>
                    <button style="padding-top: 10%;" type="submit" class="btn btn-primary">Cargar corte</button>
                </div>
        @endif
    </div>
</form>
<br><br>
@if(!empty($data))
    <div class="row">
        @foreach($data as $a)
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div style="text-align: center;" class="text-md font-weight-bold text-primary text-uppercase mb-1">
                                        Tipo de precio
                                </div>
                                <br>
                                    <div style="text-align: center;"><i class="fas fa-2x text-gray-300">- {{$a['precio']}} -</i></div>
                                <br><br>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total boletos vendidos
                                </div>
                                <br><i class="fas fa-ticket-alt fa-2x text-gray-300"> {{$a['total']}}</i>
                                <br><br>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Fecha
                                </div>
                                {{$a['fecha_compra']}}<br><br>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Partido
                                </div>
                                <br><i class="fas fa-clipboard-check fa-2x text-gray-300">{{$a['titulo']}}</i>
                                <br><br>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Tipo reservacion
                                </div>
                                <br><i class="fas fa-calendar-alt fa-2x text-gray-300"> {{$a['type_reservation']}}</i>
                                <br><br>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total vendido
                                </div>
                                <br><i class="fas fa-dollar-sign fa-2x text-gray-300">{{$a['total_vendido']}}</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        @endforeach
        


        {{-- <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Balance
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">50%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Pending Requests Card Example -->
        {{-- <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">18</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
@endif

@endsection