@extends('layouts.dashboard')

@section('title','Codigos de asiento')

@section('content')
<div class="shakeX-animation">
    <div class="p-3 p-md-5 mt-2 mt-md-0 bg-white rounded cursor-shadow seccion-dark animate__animated animate__fadeInLeft">
        <form action="{{route('find.seatcode.search')}}" method="GET">
            @csrf
            <div class="row mb-4 mb-md-0">
               {{--  <div class="col-md-6">
                    <label for="eventId">Partidos</label>
                    <div class="input-group mb-3">
                        <select class="form-control rounded-input input-dark" id="eventId" name="eventId" aria-label="Selecciona un partido" required>
                            <option value="" selected disabled>{{ strtoupper( "Seleccione un partido" ) }}</option>
                            @if(!empty($partidos))
                                @foreach($partidos as $partido)
                                    <option value="{{$partido->id}}">{{ strtoupper($partido->id . ' - ' . $partido->titulo) }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('eventId')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div> --}}
                <div class="col-md-6">
                    <label for="eventId" class="d-flex justify-content-start align-items-center" style="gap: 2px;">
                        <span class="d-inline-block" style="color: rgb(236, 62, 62)">* </span>
                        <span class="d-inline-block">Partidos</span>
                    </label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text label-rounded-form input-dark" for="eventId">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            </label>
                        </div>
                        <select class="form-control input-dark custom-select input-rounded-form" id="eventId" name="eventId" aria-label="Selecciona un partido" required>
                            <option value="" selected disabled>Seleccione un partido</option>
                            @if(!empty($partidos))
                                @foreach($partidos as $partido)
                                    <option value="{{$partido->id}}">{{ strtoupper($partido->id . ' - ' . $partido->titulo) }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('eventId')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
               {{--  <div class="col-md-6">
                    <label for="email">Correo electrónico</label>
                    <div class="input-group mb-3">
                        <input type="email" class="input-dark form-control rounded-input" id="email" name="email" aria-label="Ingresa tu correo electrónico" required>
                        @error('email')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div> --}}
                <div class="col-md-6">
                    <label for="email" class="d-flex justify-content-start align-items-center" style="gap: 2px;">
                        <span class="d-inline-block" style="color: rgb(236, 62, 62)">* </span>
                        <span class="d-inline-block">Correo electrónico</span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <label class="input-group-text label-rounded-form input-dark" for="email">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            </label>
                        </div>
                        <input type="email" class="input-dark form-control input-rounded-form" id="email" name="email" placeholder="Ingresa un correo electrónico" aria-label="Ingresa un correo electrónico" required>
                        @error('email')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="input-group-append">
                        <x-primary-button type="submit" text="Buscar"/>
                    </div>
                </div>
            </div>
        </form>
     </div>
</div>

     <div class="animate__animated animate__fadeInLeft">
        @isset($messageSuccess)
        <div class="mt-3">
            <x-alert-success-primary message="{{$messageSuccess}}"/>
        </div>
        @endisset
        
        @isset($errorSeatCode)
        <div class="mt-3">
            <x-alert-danger-primary message="{{$errorSeatCode}}"/>
        </div>
        @endisset
     </div>




    @if (isset($seatCode))
        <div class="table-responsive mt-4 animate__animated animate__fadeInLeft" style="overflow-x: auto;">
            <table class="table bg-white shadow table-striped">
                <thead class="thead-dark-dark">
                    <tr>
                        <th>Evento</th>
                        <th>Correo</th>
                        <th>Código de asiento</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($seatCode->zip($seatStatus) as [$code, $status])
                        <tr>
                            <td>{{ $eventTitle->titulo }}</td>
                            <td>{{ $email }}</td>
                            <td>{{ $code }}</td>
                            <td>
                                @if ($status == 6)
                                    <span class="px-3 py-1 rounded-pill text-primary text-span-dark " style="background-color: rgba(209, 223, 250, 0.493); font-size: 15px;"> {{$status}} Verificado</span>
                                @else
                                    <span class="px-3 py-1 rounded-pill text-danger text-span-dark bg-span-dark" style="background-color: rgba(255, 208, 220, 0.527); font-size: 15px;  display: inline-block;"> {{$status}} Inactivo</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
       </div>
    @endif

@endsection
