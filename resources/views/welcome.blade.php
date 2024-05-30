
    <body class="antialiased">
        <nav class="navbar navbar-light bg-light">
          <div class="container-fluid">
            <a class="navbar-brand" href="#">
              <img src="/docs/5.0/assets/brand/bootstrap-logo.svg" alt="" width="30" height="24" class="d-inline-block align-text-top">
              Bootstrap
            </a>
             @if (Route::has('login'))
                <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
                    @auth
                        <button href="{{ url('/home') }}" class="text-sm text-gray-700 underline">{{('Home')}}</button>
                    @else
                        <form method="POST" action="/login">
                            @csrf
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <button  type="submit" class="btn btn-light">{{ ('Iniciar sesión') }}</button>
                        </form>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline">Register</a>
                        @endif
                    @endauth
                </div>
            @endif
          </div>
        </nav>
        <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
           
        </div>
    </body>
</html>
