@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => 'https://halconesdexalapa.com.mx'])
            {{ ('Soporte Halcones') }}
        @endcomponent
    @endslot
    Hemos recibido su solicitud de compra, será entregada en un periodo máximo de 5 días, podrá realizar el seguimiento de su pedido con el siguiente folio:
    <br>
    {{ $pedido->num_control }} <br><br>
    Detalles de la compra: <br>
    @component('mail::table')
        | Artículo              | Talla         | Cantidad     | Precio       | Subtotal      |
        | ----------------------|:-------------:|:------------:|:------------:|:-------------:|
        @foreach($pedido->productos as $articulo)
            | {{ $articulo->title  }} | {{ $articulo->titulo_talla  }} | {{ $articulo->cant_total  }} | {{ '$'.$articulo->price  }} | {{ '$'.($articulo->cant_total * $articulo->price)  }} |
        @endforeach
    @endcomponent
    @if(!is_null($pedido->paqueteria)) Precio de envio: {{'$'.$pedido->paqueteria->precioEnvio}} <br> @endif
    Total: {{'$'.$pedido->total}} <br>
    @if(!is_null($pedido->paqueteria)) Paquetería: <br> {{$pedido->paqueteria->nombre}} <br> <br> @endif
    Gracias por su preferencia.
    @slot('footer')
        @component('mail::footer')
            {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        @endcomponent
    @endslot
@endcomponent
