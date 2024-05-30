<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Response;

// use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Mail;
use JWTAuth;
use DB;
use Carbon\CaIlluminaterbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;
use Illuminate\Foundation\Auth\ResetsPasswords;

use App\Models\User as U;
use App\Mail\RegistroMail;
use App\Models\SorteoUsuario;

class AuthController extends Controller{

    use ResetsPasswords;


    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['login','register','postEmail','sendPasswordResetEmail','showPasswordResetForm']]);
    }


    public function postEmail(Request $request){
      //$this->validate($request, ['correo' => 'required|email']);

      $response = Password::sendResetLink($request->only('correo'));

      // , function (Message $message) {
      //   $message->subject($this->getEmailSubject());
      // });
        switch ($response) {
            case Password::RESET_LINK_SENT:
            return response()->json( '' , Response::HTTP_NO_CONTENT);

            case Password::INVALID_USER:
            return response()->json( ['correo' => array(trans($response))] , Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    public function showResetForm() {
        return view('auth.passwor');
    }
    public function postReset(Request $request){
        $this->validate($request, [
            'token' => 'required',
            'correo' => 'required|email',
            'password' => 'required|confirmed',
        ]);

        $credentials = $request->only('correo', 'password', 'password_confirmation', 'token');

        $response = Password::reset($credentials, function ($user, $password) {
        $this->resetPassword($user, $password);
        });

        switch ($response) {
        case Password::PASSWORD_RESET:
        return redirect($this->redirectPath());

        default:
        return redirect()->back()
        ->withInput($request->only('email'))
        ->withErrors(['email' => trans($response)]);
        }
    }

    public function login()
    {
        $credentials = request(['correo', 'password']);

        // Validar si el usuario existe
        $uA = U::Where('correo',$credentials['correo'])->first();
        if($uA !== null){
            if($uA->status){
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
                $u = U::find(JWTAuth::user()->id);

                $u->jwt_token =$token;
                $u->save();
                return $this->respondWithToken($token);
            }
            else{
                return response()->json(['error'=>'Usuario con estatus BAJA'],500);
            }
        }
        else{
            return response()->json(['error'=>'Usuario no registrado en la plataforma'],500);
        }
    }

    public function register(Request $request)
    {
        $data = $request->all();


        try{
            DB::begintransaction();
            $user = User::create([
                'nombre' => $data['nombre'],
                'correo' => $data['correo'],
                'apellidoM' => $data['apellidoM'],
                'apellidoP' => $data['apellidoP'],
                'id_rol' => $data['id_rol'],
                'sexo' => $data['sexo'],
                'password' => Hash::make($data['password']),
            ]);

            if ( $request->has('addImage') && !$request->get('addImage') ) {

                // ZurielDA
                // Se establece imagen por default para el perfil de la app mobil.
                // La imagen que se agrege posteior a esta, se entiende que sera la imagen de membresia.
                    $newRequest = new Request;
                    $newRequest->merge(['image' => '', 'idOrigin'=> $user->id,  'type' => 'usuario']);

                    app(\App\Http\Controllers\api\ImagenesController::class)->uploadImage($newRequest);
                //

            }

            $token = JWTAuth::fromUser($user);


            $this->login();
            $this->sendMail($data['correo']);
            DB::commit();

            return $this->respondWithToken($token); //response()->json(['user' => $usuario, 'jwt_token' => ]);  //$this->respondWithToken($token);//response()->json(compact('user','token', 'usuario'),201);

        } catch(\Exception $e){
            DB::rollback();
            return response()->json(["Error"=> $e->getMessage()]);
        }
    }

    public function me()
    {
        return response()->json(JWTAuth::user());
    }

    public function payload()
    {
        return response()->json(JWTAuth::payload());
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }
    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {

        $user = U::Where('id',JWTAuth::user()->id)->With(['roles','avatar','usuarioMembresias.membresia.discount', 'sorteos' => function($sorteos)
        {
            $sorteos-> with(['evidenciaSorteoPartido' => function($evidenciaSorteoPartido)
            {
                $evidenciaSorteoPartido->with(['sorteoPartido.sorteo','sorteoPartido.partido', 'codigoEvidenciaSorteoPartido', 'multimediaEvidenciaSorteoPartido']);

            },'sorteo.sorteoPartido']);

        }, 'wallet_account.wallet_account_roles', 'pos_cash_register_active'])->first();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            // 'user' => U::Where('id',JWTAuth::user()->id)->With(['roles','avatar','usuarioMembresias.membresia.discount', 'sorteos.sorteo.sorteoPartido.partido'])->first(),
            'user' => $user,
        ]);
    }

    public function sendMail($correo) {
        $mail = Mail::to($correo)->send(new RegistroMail());
    }
}
