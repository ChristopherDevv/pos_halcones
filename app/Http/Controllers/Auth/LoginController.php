<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }
    public function username()
    {
        return 'correo';
    }
    protected function authenticated(Request $request, $user)
    {
     return [
       "usuario" => $user
     ];
    }

    protected function login(Request $request){
        return User::where('status',true)->where('correo',$request->all()['correo'])->with(['roles','avatar', 'usuarioMembresias'])->get();
    }
    protected function sendFailedLoginResponse(Request $request){
        throw ValidationException::withMessages([
            $this->username() => 'error',
        ]);
    }

    public function loginOnPlatform(Request $request){

        $credenciales = $request->validate([
            'correo' => ['required','email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        if(Auth::attempt(['correo' => $credenciales['correo'], 'password' => $credenciales['password'], 'webaccess' => 1], $remember)){
            $request->session()->regenerate();

                if(strtolower(auth()->user()->id_rol) === 'secondary'){
                    return redirect()->route('indicador');
                }

                return redirect()->intended('inicio')->withErrors([
                    'Este usuario no tiene permisos de administrador'
                ]);
        }

        return back()->withErrors([
            'email' => 'Los datos son incorrectos, intente nuevamente.',
        ]);


    }

    public function logout(Request $request)
{
    Auth::logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/');
}
}
