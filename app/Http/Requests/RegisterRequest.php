<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Normaliza antes de validar: así "ANA@test.local" y "ana@test.local" son el mismo correo (CA-01.3)
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $email = $this->input('email');

        $this->merge([
            'name' => is_string($name) ? trim($name) : $name,
            'email' => is_string($email) ? mb_strtolower(trim($email)) : $email,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'bail',
                'required',
                'string',
                'max:255',
                'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/', // exige punto en el dominio: rechaza ana@correo
                'email',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'bail',
                'required',
                'string',
                'confirmed', // compara con password_confirmation (CA-01.5)
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (
                        strlen($value) < 8
                        || ! preg_match('/[A-Z]/', $value)
                        || ! preg_match('/[a-z]/', $value)
                        || ! preg_match('/[0-9]/', $value)
                    ) {
                        $fail('La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.');
                    }
                },
            ],
            'password_confirmation' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.regex' => 'El correo electrónico no tiene un formato válido.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La contraseña y su confirmación no coinciden.',
            'password_confirmation.required' => 'Debes confirmar la contraseña.',
        ];
    }
}