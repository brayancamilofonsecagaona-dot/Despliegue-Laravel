<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecuperacionTest extends TestCase
{
    use RefreshDatabase; 

    /** @test */
    public function test_un_usuario_puede_solicitar_recuperacion_de_contrasenia()
    {
 
        User::create([
            'name' => 'Juan Prueba',
            'email' => 'juan.prueba@example.com',
            'password' => bcrypt('Juanpro45#'),
        ]);

        $data = [
            'email' => 'juan.prueba@example.com'
        ];

        $response = $this->post('/recuperacion', $data);

        $response->assertRedirect();

    
        $response->assertSessionHas('status', 'Si el correo electrónico coincide con una cuenta registrada, recibirás un enlace para restablecer tu contraseña.');
    }
}
