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
            'titulo'       => ['required', 'string', 'max:255'],
            'descripcion'  => ['nullable', 'string', 'max:1000'],
            'estado'       => ['required', Rule::in(array_keys(Tarea::ESTADOS))],
            'fecha_inicio' => ['required', 'date'],
            'fecha_limite' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required'             => 'El nombre de la tarea es obligatorio.',
            'titulo.max'                  => 'El nombre no puede superar 255 caracteres.',
            'descripcion.max'             => 'La descripción no puede superar 1000 caracteres.',
            'estado.required'             => 'Selecciona un estado.',
            'estado.in'                   => 'El estado debe ser Pendiente, En progreso o Completada.',
            'fecha_inicio.required'       => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date'           => 'La fecha de inicio no es válida.',
            'fecha_limite.required'       => 'La fecha de fin es obligatoria.',
            'fecha_limite.date'           => 'La fecha de fin no es válida.',
            'fecha_limite.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ];
    }
}