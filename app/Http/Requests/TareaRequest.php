<?php

namespace App\Http\Requests;

use App\Models\Tarea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => ['required', Rule::in(array_keys(Tarea::ESTADOS))],
            'fecha_limite' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede superar los 255 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',
            'estado.required' => 'Selecciona un estado.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'fecha_limite.date' => 'La fecha límite no es válida.',
        ];
    }
}