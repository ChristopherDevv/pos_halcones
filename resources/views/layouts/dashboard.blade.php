<!DOCTYPE html>
<html lang="en">

<head>
    @include('commons.head')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body id="page-top" class="sidebar-toggled">
    <!-- WRAPPER -->
    <div id="wrapper">
        <!-- MENÚ -->
        @include('commons.newMenu')
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content" class="nav-menu nav-menu-margin">
                <!-- NAV SUPERIOR -->
                @include('commons.toolbar')
            <!-- Modal menu logout-->
            <x-modal id="logoutModal" title="¿Listo para salir?" body="Selecciona 'Aceptar' a continuación si estás listo para terminar tu sesión actual." acceptRoute="{{ route('user.exit') }}" />
            <x-modal id="infoModal" title="Informacion de cuenta" body="User info" :user="Auth::user()"/>
            {{-- modal toolbar info --}}
            <x-modal id="infoModalSecond" title="Informacion de cuenta" body="User info" :user="Auth::user()"/>
            <x-modal id="logoutModalSecond" title="¿Listo para salir?" body="Selecciona 'Aceptar' a continuación si estás listo para terminar tu sesión actual." acceptRoute="{{ route('user.exit') }}" />
     
                <!-- VISTAS PARCIALES -->
                <div class="container-fluid mb-5" style="margin-top: 100px;">
                    <!-- CABECERA -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 animate__animated animate__fadeInLeft">
                        <h1 class="h3 mb-4 mb-md-0 style-font-h1">@yield('title')</h1>
                        <ul class="breadcrumb breadcrumb-light float-md-right rounded-pill mb-0 mt-2 mt-md-0 breadcrumb-dark">
                            <li class="breadcrumb-item">
                                <a href="{{route('inicio')}}" style="font-weight: 700; color:#d9ab2b;">
                                    <i class="fas fa-home"></i> 
                                    Inicio
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-sm">@yield('title')</li>
                        </ul>
                    </div>
                    <!-- CONTENIDO -->
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>
@include('commons.foot')
</html>
