<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function passwordResetProcess(UpdatePasswordRequest $request){
        return $this->updatePasswordRow($request)->count() > 0 ? $this->resetPassword($request) : $this->tokenNotFoundError();
      }

      // Verify if token is valid
      private function updatePasswordRow($request){
         return DB::table('password_resets')->where([
             'email' => $request->correo,
             'token' => $request->token
         ]);
      }

      // Token not found response 'auth.password'
      private function tokenNotFoundError() {
        return redirect()->route('form.password')->with(['error' => 'Ha ocurrido un error',  'token' => null]);
      }

      // Reset password
      private function resetPassword($request) {
          // find email
          $userData = User::where('correo',$request->correo);
          // update password
          $userData->update([
            'password'=> Hash::make($request->password)
          ]);
          // remove verification data from db
          $this->updatePasswordRow($request)->delete();

          // reset password response
          return redirect()->route('form.password')->with(
            [
                'token' => null,
                'success' => 'La contraseña ha sido actualizada',
            ]

          );
      }
}
