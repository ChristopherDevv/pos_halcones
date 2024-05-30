@component('mail::message')

<div style="text-align: center;">
    <img src="{{ env('APP_URL_FRONT') }}/assets/img/default/logoPdfMake.png" style="width: 170px">
</div>

<h4 style="text-align: center; font-size: 20px;"> ¿ Te lo vas a perder ? </h4>
<h3 style="text-align: center; font-size: 25px; color:#EDB424;"> CUPÓN DE DESCUENTO </h3>
<div style="text-align: center;">
    <img src="{{ env('APP_URL') }}{{$discount['path_image']}}" style="width: 150px">
</div>

@component('mail::table')
| Categoria       | Subcategoria         | Artículo  |
| :-------------: |:-------------:| :--------:|
| {{$discount['category']}}      | {{$discount['subcategory']}}      | {{ $discount['article'] }}     |
@endcomponent

<h1 style="text-align: center; font-size: 40px; margin-bottom: 0; color:#EDB424;"> -{{$discount['discount']}}% </h1>

<h6 style="text-align: center; font-size: 20px; margin-top: 0; text-transform: uppercase; margin-bottom: 20px; color:#EDB424;"> de descuento con el codigo: </h6>

<h3 style="text-align: center; font-size: 25px; background-color: #EDB424; padding-bottom: 20px; padding-top: 20px; color: #212529;"> {{$discount['code']}} </h3>

<p style="text-align: center; font-size: 15px; margin-bottom: 40px;">
    No dejes pasar el descuento
</p>

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


{{-- \Carbon\Carbon::now()->toDateString() --}}

<p style="text-transform:uppercase; text-align: justify; font-size: 9px; margin-bottom: 0;">
    valido solo en la compra del articulo mencionado. el código solo es valido hasta las {{ Carbon\Carbon::parse($discount['finished_at'])->locale('es-MX')->translatedFormat('g:i a l j F Y') }}.
</p>

@endcomponent
