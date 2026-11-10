<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class RecuperacionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * CA-03.1: La vista de recuperación se muestra correctamente.
     */
    public function test_vista_recuperacion_carga_correctamente(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertViewIs('recuperacion');
    }

    /**
     * CA-03.2 y CA-03.3: Un usuario puede solicitar recuperación de contraseña.
     */
    public function test_un_usuario_puede_solicitar_recuperacion_de_contrasenia(): void
    {
        User::factory()->create([
            'email' => 'ana@test.local',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'ana@test.local',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }
}