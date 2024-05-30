@extends('layouts.login')

@section('title','Login')

@section('content')
<div class="w-100" style="color: rgb(131, 131, 131);">
    <div class="img-form-login mb-4">
        <img class="img-fluid mx-auto d-block img-login" src="https://web.halconesdexalapa.com.mx/logos/logo.png" alt="logo image">
    </div>
    <div class="mb-5">
        <div class="d-flex align-items-center gap-5 mb-1 h-10">
            <h1 class="gradient-text" style="font-weight: 700; font-size: 2rem;">Iniciar sesión</h1>
        </div>
        <h2 style="opacity: 0.7; font-size: 1rem; font-weight: 400;">Ingrese sus credenciales para obtener acceso</h2>
    </div>
    @if($errors->any())
        <h4 class="text-center">{{$errors->first()}}</h4>
    @endif

    <form action="user/validate" method="POST">
        @csrf
        <div class="w-100 d-block mb-4">
            <label for="loginemail" class="d-flex justify-content-start align-items-center" style="gap: 2px;">
                <span class="d-inline-block" style="color: rgb(236, 62, 62)">* </span>
                <span class="d-inline-block">Correo electrónico</span>
            </label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <label class="input-group-text label-rounded-form " for="loginemail">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    </label>
                </div>
                <input type="email" class=" form-control input-rounded-form" id="loginemail" name="correo" required placeholder="Ingresa un correo electrónico" aria-label="Ingresa un correo electrónico" required>
            </div>
        </div>
        <div class="w-100 d-block mb-4">
            <label for="loginpassword" class="d-flex justify-content-start align-items-center" style="gap: 2px;">
                <span class="d-inline-block" style="color: rgb(236, 62, 62)">* </span>
                <span class="d-inline-block">Contraseña de cuenta</span>
            </label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <label class="input-group-text label-rounded-form " for="loginpassword">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </label>
                </div>
                <input type="password" required class="form-control  input-rounded-form" id="loginpassword" name="password" placeholder="•••••••••" aria-label="Ingresa un codigo de asiento" required>
            </div>
        </div>
        <div class="access-login">
            <div class="d-block">
                {{-- user/new --}}
                <a href="#" class="text-xs m-0 p-0" style="color:rgb(70, 70, 70); font-size: 0.75rem;">¿Aun no tienes una cuenta? <span class="text-primary ml-1" class="color: #007bff;">Solicita una qui.</span></a>
            </div>
            <div class="row">
                <div class="col">
                    <div class="input-group-append d-inline-block">
                        <x-primary-button type="submit" text="Ingresar"/>
                    </div>
                </div>
            </div>
        </div>
        
    </form>
</div>
{{-- <div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-6 d-flex align-items-center justify-content-center">
                        <img class="img-fluid mx-auto d-block img-login" src="https://web.halconesdexalapa.com.mx/logos/logo.png" alt="logo image" style="width: 60%;">
                    </div>
                    <div class="col-lg-6">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Bienvenido!</h1>
                            </div>
                            <form class="user" method="POST" action="user/validate">
                                @csrf
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-user" id="Email" name="correo" required placeholder="Correo electrónico...">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control form-control-user" id="Password" name="password" required placeholder="Password...">
                                </div>
                                <div class="form-group ml-3">
                                    <div class="custom-control custom-switch d-flex align-items-center">
                                        <input type="checkbox" class="custom-control-input" id="customCheck" name="remember" checked>
                                        <label class="custom-control-label" for="customCheck" style="font-size: 13px;">Recordar sesion</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block">Entrar</button>
                                <!--<hr>
                                <a href="index.html" class="btn btn-google btn-user btn-block"><i class="fab fa-google fa-fw"></i> Login with Google</a>
                                <a href="index.html" class="btn btn-facebook btn-user btn-block"><i class="fab fa-facebook-f fa-fw"></i> Login with Facebook</a>-->
                            </form>
                            <hr>
                            @if($errors->any())
                                <h4 class="text-center">{{$errors->first()}}</h4>
                            @endif
                            <!--<div class="text-center">
                                <a class="small" href="forgot-password.html">Forgot Password?</a>
                            </div>-->
                            <div class="text-center">
                                <a class="small" href="user/new">Solicita tu cuenta!</a>
                            </div>
                            <br>
                            <br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}

@endsection
