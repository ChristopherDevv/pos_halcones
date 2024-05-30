@component('mail::message')

<div style="text-align: center;">
    <img src="{{ env('APP_URL_FRONT') }}/assets/img/default/logoPdfMake.png" style="width: 170px">
</div>
<br>
<div style="text-align: center;">
    <span style="font-size: 18px; text-transform: uppercase; font-weight: bold;"> MEMBRESIA COMPRADA </span>
    <br>
    <div style="text-align: center;">
        <img src="{{ env('APP_URL') }}{{ $order['membresia']['membresia']['imagenes'][0]['uri_path'] }}" style="width: 300px; box-shadow: 0 0.05rem 0.5rem #592364;">
    </div>
    <br>
    <span style="font-size: 30px; color:#EDB424; text-transform: uppercase; font-weight: bold;"> {{$order['membresia']['membresia']['name']}} </span>
    <br>
    <p style="font-size: 12px; text-transform:uppercase; text-align: justify;">
        {{$order['membresia']['membresia']['description']}}
    </p>
</div>

@php
    $status = EstatusOrdenesEnum::statusMembership( $order['status'] );

    $description = "";

    switch ($order['status']) {
        case 3:
            $description = "Los beneficios de la membresia ya se aplican en su cuenta de la App Halcones. Queda al pendiente de la generación de su credencial de membresia, se le notificara cuando pueda pasar a recojer su mebresia. ";
            break;

        case 5:

            $titulo = $order['sucursal']['titulo'];
            $calle = $order['sucursal']['direccion']['calle'];
            $numExte = $order['sucursal']['direccion']['numExt'];
            $colonia = $order['sucursal']['direccion']['colonia'];
            $cp = $order['sucursal']['direccion']['cp'];
            $ciudad = $order['sucursal']['direccion']['ciudad']['nombre'];
            $estado = $order['sucursal']['direccion']['estado']['nombre'];
            $pais = "México";

            $direccionEntrega = $calle." ".$numExte.", ".$colonia .", ".$cp ." ".$ciudad.", ".$estado.", ".$pais.".";

            $description = "Su credencial esta lista. Puede pasar a recojer la credencial en la dirección:  ".$direccionEntrega ;

            break;

        case 6:
            $description = "Su credencial ha sido recogida.";
            break;
    }

@endphp

<span style="font-size: 13px; text-transform: uppercase; font-weight: bold;">Estatus:</span> <span style="font-size: 13px;"> {{ $status }} </span>

<p style="font-size: 12px; text-align: justify;">
    <span style="font-size: 13px; text-transform: uppercase; font-weight: bold;">Descriptión:</span> <span style="font-size: 13px;"> {{ $description }} </span>
</p>

<div style="display: flex!important; justify-content: space-between!important;">
    <div>
        <span style="font-size: 13px; text-transform: uppercase; font-weight: bold;">Folio:</span> <span style="font-size: 13px; text-transform: uppercase;">{{ $order['num_control'] }}</span>
    </div>
    <div>
        <span style="font-size: 13px; text-transform: uppercase; font-weight: bold;">Fecha:</span> <span style="font-size: 13px; text-transform: uppercase;">{{ Carbon\Carbon::parse( $order['updated_date'])->locale('es-MX')->translatedFormat('d-m-Y') }}</span>
    </div>
</div>
<br>
<span style="font-size: 13px; text-transform: uppercase; font-weight: bold;"> Detalles de Compra</span>
<br>
@component('mail::table')
|    <span style="font-size: 12px; text-transform: uppercase; font-weight: bold;"> Cantidad </span> | <span style="font-size: 12px; text-transform: uppercase; font-weight: bold;"> Precio </span> | <span style="font-size: 12px; text-transform: uppercase; font-weight: bold;"> Comprada </span> | <span style="font-size: 12px; text-transform: uppercase; font-weight: bold;"> Vence </span> |
| :-------------: |:----------------:| :--------: | :--------: | :--------: |
|        1        |$MXN {{ $order['membresia']['price'] }} | {{ Carbon\Carbon::parse( $order['creation_date'])->locale('es-MX')->translatedFormat('d-m-Y') }} | {{ Carbon\Carbon::createFromDate( Carbon\Carbon::now()->addYear()->year ,1,1)->translatedFormat('d-m-Y') }}  |
@endcomponent
<br>
<br>

<div style="text-align: center;">
    <img src="{{ env('APP_URL_FRONT') }}/assets/img/default/halcones-name.png" style="width: 150px;" alt="nombre halcones"/>
</div>

<div style="text-align: center;">
<div style="text-align: center; display: inline-flex;">

@component('mail::button', ['url' => 'https://play.google.com/store/apps/details?id=web.halconesdexalapa.com.mx&pli=1', 'color' => 'whithe'])
    <img src="{{ env('APP_URL_FRONT') }}/assets/img/default/google-play-badge.png" style="width: 140px;" alt="Badge de google play"/>
@endcomponent

@component('mail::button', ['url' => 'https://apps.apple.com/mx/app/halcones-de-xalapa/id1570289998', 'color' => 'whithe'])
    <img src="{{ env('APP_URL_FRONT') }}/assets/img/default/app_store_badge_es.png" style="width: 140px;" alt="Badge de appStore"/>
@endcomponent

</div>
</div>

<p style="text-transform:uppercase; text-align: justify; font-size: 9px; margin-bottom: 0;">
     <strong>Nota:</strong> Todos los beneficios que proporciona esta membresia stán sujetos a modificaciones sin previo aviso.
</p>

@endcomponent
