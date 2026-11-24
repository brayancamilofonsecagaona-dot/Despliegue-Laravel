<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TareaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'titulo' => fake()->sentence(3),
            'descripcion' => fake()->sentence(),
            'estado' => 'pendiente',
            'fecha_limite' => null,
        ];
    }
}