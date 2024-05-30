@component('mail::message')
    Su pedido con folio: {{$folio}} ha sido entregado en la sucursal: {{$sucursal->titulo}}, con direccion: {{$direccion}}.<br>
    Para dudas o aclaraciones, favor de enviar un mensaje al siguiente correo: soporte_hdx@halconesdexalapa.com.mx <br>
    Gracias por su confianza.
    <img src="https://web.halconesdexalapa.com.mx/logos/logo.png">
@endcomponent
