<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistroTest extends TestCase
{
    use RefreshDatabase;

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'name' => 'Ana Pérez',
            'email' => 'ana@correo.com',
            'password' => 'Clave1234',
            'password_confirmation' => 'Clave1234',
        ], $cambios);
    }

    public function test_el_formulario_de_registro_carga(): void
    {
        $this->get(route('register'))
            ->assertStatus(200)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }

    public function test_registro_valido_crea_usuario_con_correo_en_minusculas(): void
    {
        $response = $this->post('/registro', $this->datosValidos(['email' => 'Ana@Correo.COM']));

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('users', ['email' => 'ana@correo.com']);
    }

    public function test_la_contrasena_se_guarda_cifrada(): void
    {
        $this->post('/registro', $this->datosValidos());

        $user = User::where('email', 'ana@correo.com')->firstOrFail();
        $this->assertNotSame('Clave1234', $user->password);
        $this->assertTrue(Hash::check('Clave1234', $user->password));
    }

    public function test_campos_vacios_muestran_errores_y_no_crean_usuario(): void
    {
        $this->post('/registro', [])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_correo_duplicado_se_rechaza_sin_importar_mayusculas(): void
    {
        User::factory()->create(['email' => 'ana@correo.com']);

        $this->post('/registro', $this->datosValidos(['email' => 'ANA@correo.com']))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_correo_sin_punto_en_el_dominio_se_rechaza(): void
    {
        $this->post('/registro', $this->datosValidos(['email' => 'ana@correo']))
            ->assertSessionHasErrors('email');
    }

    #[DataProvider('contrasenasInvalidas')]
    public function test_contrasenas_que_no_cumplen_las_reglas_se_rechazan(string $clave): void
    {
        $this->post('/registro', $this->datosValidos([
            'password' => $clave,
            'password_confirmation' => $clave,
        ]))->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public static function contrasenasInvalidas(): array
    {
        return [
            'siete caracteres' => ['Corta1A'],
            'sin mayuscula'    => ['sinmayuscula1'],
            'sin minuscula'    => ['SINMINUSCULA1'],
            'sin numero'       => ['SinNumeros'],
        ];
    }

    public function test_la_confirmacion_debe_coincidir(): void
    {
        $this->post('/registro', $this->datosValidos(['password_confirmation' => 'Otra1234']))
            ->assertSessionHasErrors('password');
    }
}