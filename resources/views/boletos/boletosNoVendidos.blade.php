@extends('layouts.dashboard')

@section('title','Boletos no vendidos en APP')

@section('content')
    <div class="shakeX-animation">
        <div class="p-3 p-md-5 mt-2 mt-md-0 bg-white rounded cursor-shadow seccion-dark animate__animated animate__fadeInLeft">
            <form action="{{route('boletos.no.vendidos.search')}}" method="GET">
                @csrf
                <div class="row mb-2 mb-md-0">
                  {{--   <div class="col-md-6">
                        <label for="eventId">Partidos</label>
                        <div class="input-group mb-3">
                            <select class="input-dark form-control rounded-input" id="eventId" name="eventId" aria-label="Selecciona un partido" required>
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
    
                    <div class="col mb-4 mb-md-0">
                        <label for="eventId" class="d-flex justify-content-start align-items-center" style="gap: 2px;">
                            <span class="d-inline-block" style="color: rgb(236, 62, 62)">* </span>
                            <span class="d-inline-block">Partidos</span>
                        </label>
                        <div class="input-group ">
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
    
                    <div class="col-md-6">
                        <div class="pl-0 pl-md-4 mb-3 bg-white rounded mb-2 ml-auto w-100 w-md-50 seccion-dark">
                            <label class="d-flex justify-content-start align-items-center" style="gap: 2px;">
                                <span class="d-inline-block" style="color: rgb(236, 62, 62)">* </span>
                                <span class="d-inline-block">Tickets filtrados por fecha</span>
                            </label>
                            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                             <div class="d-flex  flex-column flex-md-row align-items-start justify-content-between w-100" style="gap: 20px;">
                                 <div class="custom-control custom-radio">
                                     <input type="radio" class="custom-control-input" id="bydate20" name="bydate" value="20" {{!isset($dateSelected) || $dateSelected == '20' ? 'checked' : ''}}  style="cursor: pointer;">
                                     <label class="custom-control-label font-weight-bold" for="bydate20" style="cursor: pointer; font-size: 15px;">
                                         Ultimos 20 minutos
                                     </label>
                                 </div>
                                 <div class="custom-control custom-radio">
                                     <input type="radio" class="custom-control-input" id="bydate1" name="bydate" value="1"  {{isset($dateSelected) && $dateSelected == '1' ? 'checked' : ''}}  style="cursor: pointer;">
                                     <label class="custom-control-label font-weight-bold" for="bydate1" style="cursor: pointer; font-size: 15px;">
                                         Ultima hora
                                     </label>
                                 </div>
                                 <div class="custom-control custom-radio">
                                     <input type="radio" class="custom-control-input" id="bydate24" name="bydate" value="24"  {{isset($dateSelected) && $dateSelected == '24' ? 'checked' : ''}}  style="cursor: pointer;">
                                     <label class="custom-control-label font-weight-bold" for="bydate24" style="cursor: pointer; font-size: 15px;">
                                        Ultimas 24 horas
                                     </label>
                                 </div>
                                 <div class="custom-control custom-radio">
                                     <input type="radio" class="custom-control-input" id="bydate0" name="bydate" value="0"  {{isset($dateSelected) && $dateSelected == '0' ? 'checked' : ''}}  style="cursor: pointer;">
                                     <label class="custom-control-label font-weight-bold" for="bydate0" style="cursor: pointer; font-size: 15px;">
                                        Todos
                                     </label>
                                 </div>
                             </div>
    
                            </div>
                             
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
        @isset($messageSuccessCancelation)
        <div class="mt-3">
            <x-alert-success-primary message="{{$messageSuccessCancelation}}"/>
        </div>
        @endisset
    
        @isset($tickets)
    
            @if($tickets->isEmpty())
                @isset($messageError)
                <div class="mt-3">
                    <x-alert-warning-primary message="{{$messageError}}"/>
                </div>
                @endisset
    
            @else
    
            @isset($messageSuccess)
            <div class="mt-3">
                <x-alert-success-secondary message="{{$messageSuccess}}"/>
            </div>
            @endisset
    </div>
   
            <div class="table-responsive mb-2 animate__animated animate__fadeInLeft" style="overflow-x: auto;">
                <div class="p-3 bg-white d-inline-flex mb-3 seccion-dark rounded">
                    <span class="text-lg text-primary" style="font-weight: 600;">Cancelacion de boletos</span>
            </div>
                <table class="table bg-white shadow table-striped">
                    <thead class="thead-dark-dark">
                        <tr>
                            <th>Ticket ID</th>
                            <th>Lugar</th>
                            <th>Fecha de creacion</th>
                            <th>Pagado</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket['ticket_id'] }}</td>
                                <td>{{ $ticket['lugar'] }}</td>
                                <td>{{ $ticket['creation_date'] }}</td>
                                <td>
                                    @if ($ticket['paid_ticket'] != 0)
                                    <span class="px-3 py-1 rounded-pill  text-sm text-span-dark text-primary" style="background-color: rgba(209, 223, 250, 0.493); font-size: 15px; display: inline-block;"> {{$ticket['paid_ticket']}}: Pagado</span>
                                    @else
                                        <span class="px-3 py-1 rounded-pill text-danger text-span-dark bg-span-dark" style="background-color: rgba(255, 208, 220, 0.527); font-size: 15px; display: inline-block;"> {{$ticket['paid_ticket']}}: No pagado</span>
                                    @endif
                                </td>
                                <td>{{ $ticket['ticket_total'] }}</td>
                                <td>
                                    <form action="{{route('cancel.ticket.novendido.web')}}" method="POST">
                                        @method('DELETE')
                                        @csrf
                                        <input type="hidden" name="UnsoldTicket" value="true">
                                        <input type="hidden" name="ticketId" value="{{ $ticket['ticket_id'] }}">
                                        <button onclick="return confirm('¿Estas seguro de cancelar este ticket con id: {{$ticket['ticket_id']}}?')" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash fa-sm"></i> Cancelar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form action="{{route('cancel.all.ticket.web')}}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="all_tickets" value="{{ json_encode($tickets) }}">
                <button onclick="return confirm('¿Estas seguro de cancelar todos los tickets?')" type="submit" class="btn btn-danger py-2 px-3 rounded-pill">Cancelar todos</button>
            </form>
        @endif
@endisset


@endsection