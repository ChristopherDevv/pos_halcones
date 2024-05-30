<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function messages() {
        return [
            'email'    => 'El :attribute debe de ser valido',
            'required'    => 'Esta sección es requerido',
            'confirmed' => 'Las contraseñas deben de coincidir'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'correo' => 'required|email',
            'password_confirmation' => 'required',
            'password' => 'required|confirmed'
        ];
    }
}
