<!DOCTYPE html>
<html lang="en">
<head>
    @include('commons.head')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="">
    <section class="bg-white" style="min-height: 100vh; width: 100%; overflow: hidden; display: flex; justify-content: center; align-items: center; flex-direction: column-reverse; flex-direction: row;">
        <div class="d-none d-lg-flex flex-column justify-content-between bg-section-login">            
            <div class="w-100 h-auto d-none d-lg-block">
                <h4 class="font-weight-bold text-white" style="opacity: 0.6;">Halcones de Xalapa.</h4>
            </div>
            <div class="h-auto w-100 text-white">
                <div>
                    <img class="img-fluid mx-auto d-block img-login" src="https://web.halconesdexalapa.com.mx/logos/logo.png" alt="logo image" style="width: 60%;">
                </div>
            </div>
            <div class="w-100">
                <p class="text-white text-sm text-center mb-4">© 2024 All rights reserved. Designed by <a href="#" class="text-white" style="opacity: 0.7; transition: all 0.3s;">Halcones de Xalapa</a></p>
                <div class="d-flex align-items-center justify-content-center text-center" style="gap: 10px;">
                    <a href="#" class="text-white" style="opacity: 0.6; transition: all 0.3s;">Legal</a>
                    <a href="#" class="text-white" style="opacity: 0.6; transition: all 0.3s;">Privacity</a>
                </div>
            </div>
        </div>
        <div class="auth-principal animate__animated animate__fadeInDown">
            <div class="auth-content">
                @yield('content')
            </div>
        </div>
    </section>
</body>
@include('commons.foot')
</html>
