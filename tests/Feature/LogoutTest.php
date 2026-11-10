<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * CA-04.1: El usuario autenticado dispone de una opción para cerrar sesión en /privada.
     */
    public function test_usuario_autenticado_ve_opcion_cerrar_sesion_en_area_privada(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@test.local',
        ]);

        $response = $this->actingAs($user)->get('/privada');

        $response->assertStatus(200);
        $response->assertViewIs('privada');
        $response->assertSee('Cerrar sesión'); 
    }

    /**
     * CA-04.2, CA-04.3 y CA-04.4: Al cerrar sesión se invalida la sesión,
     * redirige a /login y bloquea el acceso posterior a /privada.
     */
    public function test_cerrar_sesion_invalida_sesion_y_bloquea_area_privada(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@test.local',
        ]);

        // CA-04.2: Petición POST a /logout
        $response = $this->actingAs($user)->post(route('logout'));

        // Verifica que la sesión fue invalidada
        $this->assertGuest();

        // CA-04.3: Redirección al login
        $response->assertRedirect('/login');

        // CA-04.4: Intentar acceder a /privada después de cerrar sesión redirige a /login
        $protectedResponse = $this->get('/privada');
        $protectedResponse->assertRedirect('/login');
    }
}