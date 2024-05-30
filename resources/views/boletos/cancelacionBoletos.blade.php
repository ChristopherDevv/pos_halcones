@extends('layouts.dashboard')

@section('title','Cancelacion y actualizacion') 

@section('content')
<script>
    window.Laravel = {!! json_encode([
        'csrfToken' => csrf_token(),
        'tipoPagoRoute' => route('tipo.pago.web'),
    ]) !!};
</script>

<div class="shakeX-animation">
    <div class="p-3 p-md-5 mt-2 mt-md-0 bg-white rounded cursor-shadow seccion-dark animate__animated animate__fadeInLeft">
        <form action="{{route('ticket.seatcodes.web')}}" method="GET">
            @csrf
            <div class="row mb-4 mb-md-0">
                {{-- <div class="col-md-6">
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
    
              {{--   <div class="col-md-6">
                    <label for="seatCode">Codigo de asiento</label>
                    <div class="input-group mb-3">
                        <input type="text" class="input-dark form-control rounded-input" id="seatCode" name="seatCode" aria-label="Ingresa un codigo de asiento" required>
                        @error('seatCode')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div> --}}
    
                <div class="col-md-6">
                    <label for="seatCode" class="d-flex justify-content-start align-items-center" style="gap: 2px;">
                        <span class="d-inline-block" style="color: rgb(236, 62, 62)">* </span>
                        <span class="d-inline-block">Codigo de asiento</span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <label class="input-group-text label-rounded-form input-dark" for="seatCode">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
                            </label>
                        </div>
                        <input type="text" class="form-control input-dark input-rounded-form" id="seatCode" name="seatCode" placeholder="Ingresa un codigo de asiento" aria-label="Ingresa un codigo de asiento" required>
                        @error('seatCode')
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
        <x-alert-success-secondary message="{{$messageSuccess}}"/>
    </div>
    @endisset
    
    @isset($messageSuccessPayment)
    <div class="mt-3">
        <x-alert-success-secondary message="{!! $messageSuccessPayment !!}"/>
    </div>
    @endisset
    
    @isset($errorSeatCode)
    <div class="mt-3">
        <x-alert-danger-primary message="{{$errorSeatCode}}"/>
    </div>
    @endisset
    
    @isset($errorMessage)
    <div class="mt-3">
        <x-alert-danger-primary message="{{$errorMessage}}"/>
    </div>
    @endisset
    
</div>

@if (isset($tickets))

    @php
         $ticket = $tickets->first();
    @endphp

    <div class="animate__animated animate__fadeInLeft d-flex flex-column flex-md-row align-items-start justify-content-between mt-5 mb-3" style="gap: 50px;">
        
        <div class="w-100 w-md-50">
            <x-alert-success-primary message="Asientos encontrados con exito para el ticket: {{ $ticket['ticket_id'] }}"/>
        </div>
        
    
        <div class="p-3 pl-4 mb-3 border-gradiente4 rounded mb-2 ml-auto w-100 w-md-50 cursor-shadow seccion-dark bg-white">
           <div class="p-2 d-inline-flex mb-2 seccion-dark">
                <span class="text-lg" style="font-weight: 600;">Metodo de pago</span>
           </div>
           <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
            <div class="d-flex align-items-center" style="gap: 20px;">
              
                <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="payment1_{{ $ticket['ticket_id'] }}" name="payment[{{ $ticket['ticket_id'] }}]" value="1" {{ $ticket['ticket_type_payment'] == 1 ? 'checked' : '' }} style="cursor: pointer;">
                    <label class="custom-control-label" for="payment1_{{ $ticket['ticket_id'] }}" style="cursor: pointer;">
                        Efectivo
                    </label>
                </div>
                <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="payment2_{{ $ticket['ticket_id'] }}" name="payment[{{ $ticket['ticket_id'] }}]" value="2" {{ $ticket['ticket_type_payment'] == 2 ? 'checked' : '' }} style="cursor: pointer;">
                    <label class="custom-control-label" for="payment2_{{ $ticket['ticket_id'] }}" style="cursor: pointer;">
                        Tarjeta
                    </label>
                </div>
                <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="payment3_{{ $ticket['ticket_id'] }}" name="payment[{{ $ticket['ticket_id'] }}]" value="3" {{ $ticket['ticket_type_payment'] == 3 ? 'checked' : '' }} style="cursor: pointer;">
                    <label class="custom-control-label" for="payment3_{{ $ticket['ticket_id'] }}" style="cursor: pointer;">
                        Cortesía
                    </label>
                </div>
            </div>
            <div>
                <button type="button" class="mt-3 mt-md-0 btn btn-primary font-weight-bold py-2 px-4 rounded-pill disabled bg-button" data-toggle="modal" data-target="#typePayment" style="font-size: 14px; cursor: not-allowed;" disabled> Actualizar </button>
            </div>
           </div>
            
        </div>
    </div>

    <x-modal id="typePayment" title="¿Estas seguro de cambiar el tipo de pago?" body="Selecciona 'Aceptar' a continuación si estás seguro de cambiar el tipo de pago." eventSelected="{{$eventSelected}}"/>

    <div class="animate__animated animate__fadeInLeft d-flex flex-column flex-md-row align-items-start justify-content-between w-100 mb-4" style="gap: 40px;">
        <div class="cursor-shadow w-100 rounded p-3 border-gradiente2 seccion-dark bg-white">
            <div class="py-2 d-inline-flex mb-2">
                <span class="text-lg" style="font-weight: 600;">Tipo de acuerdo</span>
           </div>
           <div class="d-flex flex-column flex-md-row align-items-center justify-content-between" style="gap: 10px;">
            <p class="px-3 py-1 rounded mb-0" style="font-weight: 600; background-color: rgba(94, 92, 92, 0.1) !important;">{{ $ticket['ticket_type_agreement'] ?? 'Sin tipo' }}</p>
            <button type="button" class="mt-3 mt-md-0 btn btn-primary font-weight-bold py-2 px-4 rounded-pill bg-button" data-toggle="modal" data-target="#typeAgreement" style="font-size: 14px;"> Actualizar </button>
        </div>
        </div>
        <div class="cursor-shadow w-100 rounded p-3 border-gradiente3 seccion-dark bg-white">
            <div class="py-2 d-inline-flex mb-2">
                <span class="text-lg" style="font-weight: 600;">Tipo de reservacion</span>
           </div>
           <div class="d-flex flex-column flex-md-row align-items-center justify-content-between" style="gap: 10px;">
            <p class="px-3 py-1 rounded mb-0" style="font-weight: 600; background-color: rgba(94, 92, 92, 0.1) !important;">{{ $ticket['ticket_type_reservation'] ?? 'Sin tipo' }}</p>
            <button type="button" class="mt-3 mt-md-0 btn btn-primary font-weight-bold py-2 px-4 rounded-pill bg-button" data-toggle="modal" data-target="#typeReservation" style="font-size: 14px;"> Actualizar </button>
        </div>
        </div>
        <div class="cursor-shadow w-100 rounded p-3 border-gradiente seccion-dark bg-white">
            <div class="py-2 d-inline-flex mb-2">
                <span class="text-lg" style="font-weight: 600;">Total ticket</span>
           </div>
           <div class="d-flex flex-column flex-md-row align-items-center justify-content-between" style="gap: 10px;">
            <p class="px-3 py-1 rounded mb-0" style="font-weight: 600; background-color: rgba(94, 92, 92, 0.1) !important;">{{ $ticket['ticket_total'] ?? 'Sin total' }}</p>
            <button type="button" class="mt-3 mt-md-0 btn btn-primary font-weight-bold py-2 px-4 rounded-pill bg-button" data-toggle="modal" data-target="#ticketTotal" style="font-size: 14px;"> Actualizar </button>
        </div>
        </div>
    </div>

    <x-text-area-modal id="typeAgreement" title="¿Estas seguro de actualizar el 'Tipo'?" body="Ingresa el 'Tipo de acuerdo' a continuación si estás seguro" acceptRoute="{{ route('actualizar.campos.ticket.web') }}" nameTextArea="Type agreement" ticketId="{{$ticket['ticket_id']}}" fieldName="type_agreement" eventSelected="{{$eventSelected}}"/>    
    <x-select-modal id="typeReservation" title="¿Estas seguro de actualizar el 'Tipo'?" body="Ingresa el 'Tipo de reservacion' a continuación si estás seguro" acceptRoute="{{ route('actualizar.campos.ticket.web') }}" nameSelect="Type reservation" ticketId="{{$ticket['ticket_id']}}" fieldName="type_reservation" eventSelected="{{$eventSelected}}"/>    
    <x-number-modal id="ticketTotal" title="¿Estas seguro de actualizar el 'Total'?" body="Ingresa el 'Total' a continuación si estás seguro de actualizarlo" acceptRoute="{{ route('actualizar.campos.ticket.web') }}" numberName="Total" ticketId="{{$ticket['ticket_id']}}" fieldName="total" eventSelected="{{$eventSelected}}"/>    

    <div class="table-responsive mb-2 animate__animated animate__fadeInLeft" style="overflow-x: auto;">
        <div class="p-3 bg-white d-inline-flex mb-3 seccion-dark rounded">
            <span class="text-lg text-primary" style="font-weight: 600;">Cancelacion de boletos</span>
       </div>
        <table class="table bg-white shadow table-striped">
            <thead class="thead-dark-dark">
                <tr>
                    <th>Ticket ID</th>
                    <th>Ticket Status</th>
                    <th>Asiento ID</th>
                    <th>Codigo de asiento</th>
                    <th>Ticket pagado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket['ticket_id'] }}</td>
                        <td>{{ $ticket['ticket_status'] }}</td>
                        <td>{{ $ticket['seat_id'] }}</td>
                        <td>{{ $ticket['seat_code'] }}</td>
                        <td>
                            @if ($ticket['paid_ticket'] != 0)
                            <span class="px-3 py-1 rounded-pill  text-sm text-span-dark text-primary" style="background-color: rgba(209, 223, 250, 0.493); font-size: 15px; display: inline-block;"> {{$ticket['paid_ticket']}}: Pagado</span>
                            @else
                                <span class="px-3 py-1 rounded-pill text-danger text-span-dark bg-span-dark " style="background-color: rgba(255, 208, 220, 0.527); font-size: 15px; display: inline-block;"> {{$ticket['paid_ticket']}}: No pagado</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{route('delete.seat.ticket.web')}}" method="POST">
                                @method('DELETE')
                                @csrf
                                <input type="hidden" name="ticketId" value="{{ $ticket['ticket_id'] }}">
                                <input type="hidden" name="seatId" value="{{ $ticket['seat_id'] }}">
                                <input type="hidden" name="eventSelected" value="{{$eventSelected}}">
                                <button onclick="return confirm('¿Estas seguro de eliminar este asiento con id: {{ $ticket['seat_id'] }}?')" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash fa-sm"></i> Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>
        <form action="{{route('cancel.ticket.web')}}" method="POST">
            @method('DELETE')
            @csrf
            <input type="hidden" name="ticketId" value="{{ $ticket['ticket_id'] }}">
            <button onclick="return confirm('¿Estas seguro de cancelar este ticket con id: {{ $ticket['ticket_id'] }}?')" type="submit" class="btn btn-danger py-2 px-3 rounded-pill">Cancelar ticket</button>
        </form>
    </div>
@endif

@endsection
