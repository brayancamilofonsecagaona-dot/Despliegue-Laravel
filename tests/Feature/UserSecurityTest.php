<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class UserSecurityTest extends TestCase
{
    /**
     * CP-08 (CA-01.7): Las contraseñas se almacenan mediante hashing seguro, nunca en texto plano.
     */
    public function test_contrasena_se_almacena_con_hashing_seguro(): void
    {
        $passwordPlano = 'Abcd1234';
        $passwordHasheado = Hash::make($passwordPlano);

        $this->assertNotEquals($passwordPlano, $passwordHasheado);
        $this->assertTrue(Hash::check($passwordPlano, $passwordHasheado));
    }

    /**
     * CP-05 y CP-06 (CA-01.4): Valores límite de contraseña (7 caracteres falla, 8 caracteres con mayúscula, minúscula y número aprueba).
     */
    public function test_reglas_de_seguridad_y_valores_limite_de_contrasena(): void
    {
        $regla = ['password' => ['required', Password::min(8)->mixedCase()->numbers()]];

        // CP-05: 7 caracteres (Límite inválido)
        $validadorCorto = Validator::make(['password' => 'Abc1234'], $regla);
        $this->assertTrue($validadorCorto->fails());

        // CP-06: 8 caracteres con mayúscula, minúscula y número (Límite válido)
        $validadorValido = Validator::make(['password' => 'Bbcd1234'], $regla);
        $this->assertFalse($validadorValido->fails());
    }
}
