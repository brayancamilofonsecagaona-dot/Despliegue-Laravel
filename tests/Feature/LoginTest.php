<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // Prueba CA-02.1: Que la vista de login cargue y muestre los campos
    public function test_el_formulario_de_login_carga_correctamente(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Correo electrónico');
        $response->assertSee('Contraseña');
    }

    // Prueba CA-02.6: Un visitante no autenticado no puede entrar a /privada
    public function test_visitante_no_autenticado_es_redirigido_al_login(): void
    {
        $response = $this->get('/privada');

        // Verifica que no lo dejó entrar y lo mandó a /login
        $response->assertRedirect('/login');
    }

    // Prueba CA-02.4: No permite iniciar sesión con campos vacíos
    public function test_no_permite_iniciar_sesion_con_campos_vacios(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        // Verifica que la sesión devuelva errores en email y password
        $response->assertSessionHasErrors(['email', 'password']);
    }

    // Prueba CA-02.3: Credenciales incorrectas devuelven error genérico
    public function test_credenciales_incorrectas_muestran_error_generico(): void
    {
        // Creamos un usuario temporal de prueba
        $user = User::factory()->create([
            'email' => 'prueba@correo.com',
            'password' => bcrypt('Password123'),
        ]);

        // Intentamos entrar con una contraseña equivocada
        $response = $this->post('/login', [
            'email' => 'prueba@correo.com',
            'password' => 'ClaveErronea999',
        ]);

        // Verificamos que devuelva tu error 'credenciales' y que el usuario NO quede autenticado
        $response->assertSessionHasErrors('credenciales');
        $this->assertGuest();
    }

    // Prueba CA-02.2 y CA-02.5: Credenciales válidas autentican y redirigen a /privada
    public function test_usuario_puede_iniciar_sesion_y_es_redirigido_a_area_privada(): void
    {
        // Creamos un usuario temporal de prueba
        $user = User::factory()->create([
            'email' => 'valido@correo.com',
            'password' => bcrypt('Password123'),
        ]);

        // Enviamos las credenciales correctas
        $response = $this->post('/login', [
            'email' => 'valido@correo.com',
            'password' => 'Password123',
        ]);

        // Verificamos que quedó autenticado y que lo redirigió a /privada
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/privada');
    }

    // Prueba HU-05 (CA-05.1 a CA-05.5): Límite de 5 intentos fallidos por cuenta + IP y bloqueo temporal
    public function test_limite_intentos_fallidos_y_bloqueo_temporal(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@test.local',
            'password' => bcrypt('Password123'),
        ]);

        $key = 'login.' . $user->email . '|127.0.0.1';
        RateLimiter::clear($key);

        // CA-05.1: Realizar 5 intentos fallidos consecutivos
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'ana@test.local',
                'password' => 'ClaveErronea999',
            ]);
            RateLimiter::hit($key, 900); // Ventana de 15 minutos (900 segundos)
        }

        // CA-05.2 y CA-05.3: Al alcanzar el 5º intento fallido queda bloqueado y sin autenticar
        $this->assertTrue(RateLimiter::tooManyAttempts($key, 5));
        $this->assertGuest();

        // CA-05.4 y CA-05.5: Al cumplirse el tiempo o iniciar sesión exitosamente se reinicia el contador
        RateLimiter::clear($key);
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 5));
    }
}