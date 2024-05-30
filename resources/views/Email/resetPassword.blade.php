@component('mail::message')
<img src="https://web.halconesdexalapa.com.mx/logos/logo.png">
Soporte Halcones

Hemos recibido una solicitud de recuperación de contraseña a este correo, si la has solicitado da clic en cambiar contraseña.
En caso de que no la hallas solicitado no hagas caso a este mensaje.
<br>


@component('mail::button', ['url' =>'https://web.halconesdexalapa.com.mx/api/auth/reset-password-form?token='.$token])
    {{ ('Cambiar contraseña') }}
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
