<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvitationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre_completo' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:invitation_requests,email'],
            'edad' => ['required', 'integer', 'min:18', 'max:120'],
            'pais' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:100'],
            'municipio' => ['required', 'string', 'max:100'],
            'tipo_perfil' => ['required', 'string', 'in:single,pareja,unicornio,grupales'],
            'motivo' => ['required', 'string', 'min:20', 'max:2000'],
            'terminos_aceptados' => ['required', 'accepted'],
            'privacidad_aceptada' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya ha solicitado invitación.',
            'edad.min' => 'Debes ser mayor de 18 años.',
            'tipo_perfil.required' => 'Selecciona tu tipo de perfil.',
            'motivo.min' => 'Cuéntanos un poco más sobre ti (mínimo 20 caracteres).',
            'terminos_aceptados.accepted' => 'Debes aceptar los términos y condiciones.',
            'privacidad_aceptada.accepted' => 'Debes aceptar la política de privacidad.',
        ];
    }
}
