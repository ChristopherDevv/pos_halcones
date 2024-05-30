@extends('layouts.login')

@section('title', 'Recuperar contraseña')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Cambie su contraseña</h1>
                                </div>
                                <form class="user" method="POST" action="{{ route('change.password') }}">
                                    {!! csrf_field() !!}
                                    <input name="token" type="hidden" value="{{ $token }}" />
                                    <div class="form-group">
                                        <input type="email" name="correo" class="form-control form-control-user" id="correo"
                                            aria-describedby="emailHelp" placeholder="Correo electrónico...">
                                    </div>
                                    @error('correo')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control form-control-user"
                                            id="password" placeholder="Nueva contraseña...">
                                    </div>
                                    @error('password')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="form-group">
                                        <input type="password" name="password_confirmation"
                                            class="form-control form-control-user" id="password_confirmation"
                                            placeholder="Comfirmar contraseña...">
                                    </div>
                                    @error('password_confirmation')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <input type="submit" name="send" value="Cambiar" class="btn btn-dark btn-block">
                                </form>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        @if (Session::has('success'))
            toastr.options =
            {
            "closeButton" : true,
            "progressBar" : true
            }
            toastr.success("{{ session('success') }}");
        @endif

        @if (Session::has('error'))
            toastr.options =
            {
            "closeButton" : true,
            "progressBar" : true
            }
            toastr.error("{{ session('error') }}");
        @endif
    </script>
@endsection
