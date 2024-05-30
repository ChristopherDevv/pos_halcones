@component('mail::message')
Su pedido ha sido enviado, puede darle seguimiento con el siguiente folio: {{  $folio }}<br>
Paquteria: {{ $paqueteria->nombre}} <br>
@if($paqueteria->nombre === 'Halcones')
Para dudas o aclaraciones puede enviarnos un mesaje al siguiente correo: soporte_hdx@halconesdexalapa.com.mx
@else
Acceda a la sección de rastreo de la página oficial de la paquetería, para consultar el avance de su pedido
@endif
<img src="https://web.halconesdexalapa.com.mx/logos/logo.png">
@endcomponent
