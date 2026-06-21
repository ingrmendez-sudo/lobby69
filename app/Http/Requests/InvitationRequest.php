<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $esMexico = $this->input('pais') === 'México';

        return [
            'nombre_completo'     => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255', 'unique:users,email'],
            'edad'                => ['required', 'integer', 'min:18', 'max:120'],
            'pais'                => ['required', 'string', 'max:100'],
            'estado'              => $esMexico
                                        ? ['required', 'string', 'max:100']
                                        : ['required', 'string', 'max:100'],
            'municipio'           => ['required', 'string', 'max:100'],
            'tipo_perfil'         => ['required', 'string', 'in:single,pareja,unicornio'],
            'motivo'              => ['required', 'string', 'min:20', 'max:2000'],
            'invitation_code'     => ['nullable', 'string', 'max:20'],
            'terminos_aceptados'  => ['required', 'accepted'],
            'privacidad_aceptada' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_completo.required'    => 'El nick es obligatorio.',
            'email.required'              => 'El correo es obligatorio.',
            'email.email'                 => 'Ingresa un correo válido.',
            'email.unique'                => 'Este correo ya tiene una solicitud registrada.',
            'edad.required'               => 'La edad es obligatoria.',
            'edad.min'                    => 'Debes tener al menos 18 años.',
            'pais.required'               => 'Selecciona tu país.',
            'estado.required'             => 'El estado o región es obligatorio.',
            'municipio.required'          => 'El municipio o ciudad es obligatorio.',
            'tipo_perfil.required'        => 'Selecciona un tipo de perfil.',
            'tipo_perfil.in'              => 'Tipo de perfil no válido.',
            'motivo.required'             => 'Cuéntanos tu motivo.',
            'motivo.min'                  => 'Escribe al menos 20 caracteres.',
            'terminos_aceptados.accepted' => 'Debes aceptar los Términos y Condiciones.',
            'privacidad_aceptada.accepted'=> 'Debes aceptar la Política de Privacidad.',
        ];
    }
}
