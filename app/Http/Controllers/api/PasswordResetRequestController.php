<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class PasswordResetRequestController extends Controller
{
    public function sendPasswordResetEmail(Request $request){
        // If email does not exist
        if(!$this->validEmail($request->correo)) {
            return response()->json([
                'message' => 'El correo no existe'
            ], Response::HTTP_NOT_FOUND);
        } else {
            // If email exists
            $this->sendMail($request->correo);
            return response()->json([
                'message' => 'Revise su bandeja de entrada, hemos enviado un enlace para restablecer el correo electrónico.'
            ], Response::HTTP_OK);
        }
    }


    public function sendMail($email){
        $token = $this->generateToken($email);
        $image = asset('logos/logo.png');
        Mail::to($email)->send(new SendMail($token,$image));
    }

    public function validEmail($email) {
       return !!User::where('correo', $email)->first();
    }

    public function generateToken($email){
      $isOtherToken = DB::table('password_resets')->where('email', $email)->first();

      if($isOtherToken) {
        return $isOtherToken->token;
      }

      $token = Str::random(80);;
      $this->storeToken($token, $email);
      return $token;
    }

    public function storeToken($token, $email){
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }

    public function showPasswordResetForm(Request $request){
        $token = null;
        if($request->has('token')){
            $token = request(['token']);
            $token = $token['token'];
        }
        return view('auth.password')->with(['token'=>$token, 'error' => null, 'success'=> null]);
    }
}
