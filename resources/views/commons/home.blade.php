@extends('layouts.dashboard')

@section('title','Detalles Indicador')

@section('content')

  {{-- Selección de partido --}}
  <div class="shakeX-animation">
    <div class="p-3 p-md-5 mt-2 mt-md-0 bg-white cursor-shadow rounded seccion-dark animate__animated animate__fadeInLeft">
      <form action="indicador_carga" method="post">
        @csrf
        <div class="row">
         {{--  <div class="col">
            <label for="exampleInputEmail1"> Partidos </label>
            <div class="input-group mb-3">
                <select class="input-dark form-control rounded-input" name="idJornada" id="idJornada" aria-label="Seleccionar Partido" required>
                  <option value="" selected disabled>{{ strtoupper( "Seleccione una jornada" ) }}</option>
                  @if($partidos))
                      @foreach($partidos as $j)
                          <option value="{{$j->id}}">{{ strtoupper($j->titulo) }}</option>
                      @endforeach
                  @endif
              </select>              
            </div>
          </div>   
           --}}
          <div class="col">
            <label for="exampleInputEmail1" class="d-flex justify-content-start align-items-center" style="gap: 2px;">
                <span class="d-inline-block" style="color: rgb(236, 62, 62)">* </span>
                <span class="d-inline-block">Partidos</span>
            </label>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <label class="input-group-text label-rounded-form input-dark" for="exampleInputEmail1">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </label>
                </div>
                <select class="form-control input-dark custom-select input-rounded-form" id="exampleInputEmail1" name="idJornada" aria-label="Selecciona un partido" required>
                    <option value="" selected disabled>Seleccione un partido</option>
                    @if($partidos))
                      @foreach($partidos as $j)
                          <option value="{{$j->id}}">{{ strtoupper($j->titulo) }}</option>
                      @endforeach
                  @endif
                </select>
                @error('eventId')
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
  </div>

  
  @if(isset($data['match']))

    <div class="container-fluid mx-0 shadow seccion-dark py-2 mt-3 bg-white rounded animate__animated animate__fadeInLeft">
      
      <div class="row my-4">
        <div class="col">
            <span class="text-dark font-weight-bold">{{ strtoupper($data['match']['titulo']) }}:</span> <span class="text-muted">{{ strtoupper($data['match']['descripcion']) }}</span>
        </div>
      </div>

      <div class="row my-4">
        <div class="col">
    
          <strong> Lugar: <span class="text-dark">{{ $data['match']['lugar']}}</span></strong>
    
          &nbsp;&nbsp; <strong> Fecha: <span class="text-dark">{{ Carbon\Carbon::parse($data['match']['fecha'])->locale('es-MX')->translatedFormat('l j F Y') }}</span></strong> &nbsp;&nbsp;
    
          <strong> Hora: <span class="text-dark">{{ Carbon\Carbon::parse($data['match']['fecha'])->locale('es-MX')->translatedFormat('g:i a') }}</span></strong>
          <br>
        </div>
      </div>

      <div class="row">
        <div class="col" id="container-html">

        </div>
      </div>

    </div>

  @endif

  @if (isset($data['match']))
    
  <script>
    var data = @json($data);
    const formatMX =  (number, addMX = false) => 
    {
      let numberFormat = new Intl.NumberFormat('es-MX', { maximumFractionDigits: 2 }).format(number);

      return addMX ? `MX$ ${numberFormat}` : numberFormat;
    }

    function filterCourtesyTickets(groupTicketsSeatTypePayment, searchCourtesyTickets) 
    {
      return groupTicketsSeatTypePayment.filter((TicketsSeatTypePayment) => searchCourtesyTickets ? TicketsSeatTypePayment.payment.toLocaleLowerCase() == 'cortesía' : TicketsSeatTypePayment.payment.toLocaleLowerCase() != 'cortesía');
    }

    function getSumPropertyGroupTicketsSeatPrices(groupTicketsSeatSubscription, value)
    {
        switch (value) {
          case "quantity":
              return groupTicketsSeatSubscription.reduce((a, b) => a + b.quantity, 0);
            break;
          case "total":
              return groupTicketsSeatSubscription.reduce((a, b) => a + b.total, 0);
            break;
          default:
              return 0
            break;
        }
      }


    function dataTickets(ticketsSeat) 
    {

      let dataTicketsSeatHTML = '';

      $.each(ticketsSeat, function( indexTicketsSeat, tickets ) 
      {      
        dataTicketsSeatHTML += `        
          <div class="row">
            <div class="col shadow-sm mb-3">          
              <div class="row mb-1 mt-3">
                <div class="col">
                  <strong>Venta en ${tickets.typeReservation} </strong>
                </div>
                <div class="col">
                </div>
                <div class="col">
                </div>
              </div>

              ${
                (()=>
                {
                  let dataTicketsSeatSubscriptionHTML = '';

                  $.each(tickets.groupTicketsSeatSubscription, function( index, groupTicketsSeatSubscription ) 
                  {         
                    dataTicketsSeatSubscriptionHTML += `        
                      <div class="row my-3 mx-2 border rounded shadow-sm mb-3">            
                        <div class="col">

                          <div class="row mt-2">
                            <div class="col">
                              <strong> Venta por ${(groupTicketsSeatSubscription.isSubscription ? "Abonos" : "Partido") } </strong>
                            </div>
                            <div class="col">                        
                            </div>
                            <div class="col">                        
                            </div>
                          </div>  

                          ${
                            (()=>
                            {
                              let dataHTML = '';

                              let groupTicketsSeatTypePaymentTemp = filterCourtesyTickets(groupTicketsSeatSubscription.groupTicketsSeatTypePayment, false);

                              $.each(groupTicketsSeatTypePaymentTemp, function( indexTicketsSeatTypePayment, groupTicketsSeatTypePayment ) 
                              {      
                                dataHTML += `                                        
                                <div class="row my-3 mx-2">
                                  <div class="col-1 mx-2">
                                    <div class="h-100 d-flex align-items-center">
                                      <strong class="text-muted"> ${ groupTicketsSeatTypePayment.payment } </strong>
                                    </div>
                                  </div>
                                  <div class="col">
                                      <div class="table-responsive">
                                        <table class="table">
                                          <thead>
                                            <tr>                                              
                                              ${
                                                (()=>
                                                {

                                                  dataTH = "";                          

                                                  $.each(groupTicketsSeatTypePayment.groupTicketsSeatPrices, function( index, seat )
                                                  {
                                                    dataTH += `<th class="col-1"> ${  !indexTicketsSeatTypePayment ? formatMX(seat.price) : '' }</th>`;
                                                  });                        

                                                  dataTH += `<th class="col-1"> ${  !indexTicketsSeatTypePayment ? 'Total Boletos' : '' } </th>`;
                                                  dataTH += `<th class="col-1"> ${  !indexTicketsSeatTypePayment ? 'Total Dinero' : '' } </th>`;                        
                                                  
                                                  return dataTH;

                                                })()
                                              }
                                            </tr>
                                          </thead>
                                          <tbody>
                                            <tr>
                                            ${
                                              (()=>
                                              {
                                                dataTD = "";                          

                                                $.each(groupTicketsSeatTypePayment.groupTicketsSeatPrices, function( index, seat )
                                                {
                                                  dataTD += `<th class="col-1"> ${  seat.quantity }</th>`;
                                                });                        

                                                dataTD += `<td class="border-bottom border-top"> ${groupTicketsSeatTypePayment.totalQuantityTicketsSeat} </td>`;
                                                dataTD += `<td class="border-bottom border-top"> ${ formatMX(groupTicketsSeatTypePayment.totalSellTicketSeat, true) } </td>`;
                                                
                                                
                                                return dataTD;

                                              })()
                                            }                                              
                                            </tr>
                                          </tbody>
                                        </table>
                                      </div>
                                    </div>                                
                                </div>                                
                                `;
                              });

                              return dataHTML;
                            })()                            
                          }

                          ${
                                  (()=>{

                                    rowHTML = `
                                      <div class="row my-3 mx-2">                                      
                                        <div class="col-1 mx-2">
                                          <div class="h-100 d-flex align-items-center">
                                            <strong class=""> Total </strong>
                                          </div>
                                        </div>

                                        <div class="col">
                                          <div class="table-responsive">
                                            <table class="table">
                                              <thead>
                                                <tr>                                                
                                                  ${
                                                    (()=>
                                                    {
                                                      dataTH = "";

                                                      console.log( groupTicketsSeatSubscription.totalGroupSeatPrice );

                                                      $.each(groupTicketsSeatSubscription.totalGroupSeatPrice, function( index, groupSeatPrice )
                                                      {
                                                        dataTH += `<th class="col-1"> ${  groupSeatPrice.quantity  }</th>`;
                                                      });                                                                                                                
                                                      
                                                      dataTH += `<th class="col-1"> ${ getSumPropertyGroupTicketsSeatPrices(groupTicketsSeatSubscription.totalGroupSeatPrice, 'quantity')  } </th>
                                                                <th class="col-1">  ${ formatMX( getSumPropertyGroupTicketsSeatPrices(groupTicketsSeatSubscription.totalGroupSeatPrice, 'total'), true ) } </th>
                                                                `;

                                                      return dataTH;

                                                    })()
                                                  }
                                                </tr>
                                              </thead>
                                            </table>
                                          </div>
                                        </div>
                                      </div>
                                    `;

                                    return  filterCourtesyTickets(groupTicketsSeatSubscription.groupTicketsSeatTypePayment, false).length > 1 ? rowHTML : '';

                                  })()
                                }

                        </div>
                      </div>
                    `;
                  });

                  return dataTicketsSeatSubscriptionHTML;

                })()
              }
              
            </div>

          </div>
        `;
      });

      return dataTicketsSeatHTML;

    }

    $(document).ready(function() {
    // Comprueba si la propiedad ticketsSeat de la variable data está definida
        if (data && data.ticketsSeat) {
            // Pasa el valor de data.ticketsSeat a la función dataTickets
            // y establece el HTML resultante en el elemento con el ID container-html
            $("#container-html").html(dataTickets(data.ticketsSeat));
        }
    });

  </script>

  @endif
  
@endsection