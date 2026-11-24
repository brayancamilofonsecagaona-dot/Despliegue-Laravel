<?php

namespace Tests\Feature;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TareaReglasTest extends TestCase
{
    use RefreshDatabase;

    private function datos(array $extra = []): array
    {
        return array_merge([
            'titulo'       => 'Entregar informe',
            'descripcion'  => 'Informe final del sprint',
            'estado'       => 'pendiente',
            'fecha_inicio' => '2026-12-10',
            'fecha_limite' => '2026-12-12',
        ], $extra);
    }

    // CA-01 (positivo): crear con los cinco campos
    public function test_crea_tarea_con_los_cinco_campos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tareas.store'), $this->datos())
            ->assertRedirect(route('tareas.index'));

        $this->assertDatabaseHas('tareas', [
            'user_id' => $user->id,
            'titulo' => 'Entregar informe',
            'descripcion' => 'Informe final del sprint',
            'estado' => 'pendiente',
        ]);
    }

    // CA-02 (negativo): fecha fin anterior al inicio -> rama falsa
    public function test_rechaza_fecha_fin_anterior_al_inicio(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tareas.store'), $this->datos(['fecha_inicio' => '2026-12-12', 'fecha_limite' => '2026-12-10']))
            ->assertSessionHasErrors('fecha_limite');

        $this->assertDatabaseCount('tareas', 0);
    }

    // CA-02 (límite): fin igual al inicio -> rama verdadera
    public function test_acepta_fecha_fin_igual_al_inicio(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tareas.store'), $this->datos(['fecha_inicio' => '2026-12-10', 'fecha_limite' => '2026-12-10']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('tareas', 1);
    }

    // CA-02 / CA-11: obligatorios
    public function test_exige_nombre_y_fechas(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tareas.store'), $this->datos(['titulo' => '', 'fecha_inicio' => '', 'fecha_limite' => '']))
            ->assertSessionHasErrors(['titulo', 'fecha_inicio', 'fecha_limite']);
    }

    // CA-11: el mensaje explica qué corregir
    public function test_mensaje_de_fechas_invertidas_explica_el_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tareas.store'), $this->datos(['fecha_inicio' => '2026-12-12', 'fecha_limite' => '2026-12-10']))
            ->assertSessionHasErrors(['fecha_limite' => 'La fecha de fin no puede ser anterior a la fecha de inicio.']);
    }

    // CA-10: estado no permitido
    public function test_rechaza_estado_no_permitido(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tareas.store'), $this->datos(['estado' => 'cancelada']))
            ->assertSessionHasErrors('estado');
    }

    // CA-04: consultar datos completos
    public function test_muestra_los_datos_completos_de_una_tarea(): void
    {
        $user = User::factory()->create();
        $tarea = Tarea::factory()->create([
            'user_id' => $user->id,
            'titulo' => 'Preparar exposición',
            'descripcion' => 'Diapositivas y demo',
            'fecha_inicio' => '2026-12-01',
            'fecha_limite' => '2026-12-05',
            'estado' => 'en_progreso',
        ]);

        $this->actingAs($user)->get(route('tareas.show', $tarea))
            ->assertOk()
            ->assertSee('Preparar exposición')
            ->assertSee('Diapositivas y demo')
            ->assertSee('01/12/2026')
            ->assertSee('05/12/2026')
            ->assertSee('En progreso');
    }

    // CP-01 de la guía: usuario autenticado sin permiso -> 403
    public function test_no_puede_ver_la_tarea_de_otro_usuario(): void
    {
        $dueno = User::factory()->create();
        $otro = User::factory()->create();
        $tarea = Tarea::factory()->create(['user_id' => $dueno->id]);

        $this->actingAs($otro)->get(route('tareas.show', $tarea))->assertForbidden();
    }

    // CA-12: tarea inexistente -> 404
    public function test_operaciones_sobre_tarea_inexistente_dan_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('tareas.show', 9999))->assertNotFound();
        $this->actingAs($user)->get(route('tareas.edit', 9999))->assertNotFound();
        $this->actingAs($user)->put(route('tareas.update', 9999), $this->datos())->assertNotFound();
        $this->actingAs($user)->delete(route('tareas.destroy', 9999))->assertNotFound();
    }

    // CA-09: visitante no gestiona tareas
    public function test_visitante_no_puede_consultar_una_tarea(): void
    {
        $tarea = Tarea::factory()->create();

        $this->get(route('tareas.show', $tarea))->assertRedirect(route('login'));
    }

    // CA-03: aparece en cada día de su rango
    public function test_tarea_aparece_en_cada_dia_de_su_rango_en_el_calendario(): void
    {
        $user = User::factory()->create();
        $tarea = Tarea::factory()->create([
            'user_id' => $user->id,
            'fecha_inicio' => '2026-12-10',
            'fecha_limite' => '2026-12-12',
        ]);

        $respuesta = $this->actingAs($user)
            ->get(route('tareas.index', ['mes' => '2026-12']))
            ->assertOk();

        $dias = collect($respuesta->viewData('semanas'))->flatten(1)
            ->filter(fn ($d) => $d['delMes'] && collect($d['tareas'])->contains('id', $tarea->id))
            ->count();

        $this->assertSame(3, $dias);
    }

    // CA-03: rango que cruza de un mes a otro
    public function test_rango_que_cruza_de_mes_se_ve_en_ambos_meses(): void
    {
        $user = User::factory()->create();
        Tarea::factory()->create([
            'user_id' => $user->id,
            'titulo' => 'Tarea cruzada',
            'fecha_inicio' => '2026-11-29',
            'fecha_limite' => '2026-12-02',
        ]);

        $this->actingAs($user)->get(route('tareas.index', ['mes' => '2026-11']))->assertSee('Tarea cruzada');
        $this->actingAs($user)->get(route('tareas.index', ['mes' => '2026-12']))->assertSee('Tarea cruzada');
        $this->actingAs($user)->get(route('tareas.index', ['mes' => '2027-02']))->assertDontSee('Tarea cruzada');
    }

    // CA-06: lo editado se refleja en el listado
    public function test_los_cambios_editados_se_reflejan_en_el_listado(): void
    {
        $user = User::factory()->create();
        $tarea = Tarea::factory()->create([
            'user_id' => $user->id,
            'fecha_inicio' => '2026-12-10',
            'fecha_limite' => '2026-12-12',
        ]);

        $this->actingAs($user)
            ->put(route('tareas.update', $tarea), $this->datos(['titulo' => 'Nombre actualizado']))
            ->assertRedirect(route('tareas.index'));

        $this->actingAs($user)
            ->get(route('tareas.index', ['mes' => '2026-12']))
            ->assertSee('Nombre actualizado');
    }

    // CA-07: la eliminación pide confirmación
    public function test_el_boton_eliminar_pide_confirmacion(): void
    {
        $user = User::factory()->create();
        Tarea::factory()->create(['user_id' => $user->id, 'fecha_inicio' => null, 'fecha_limite' => null]);

        $this->actingAs($user)->get(route('tareas.index'))->assertSee('confirm(', false);
    }

    // CA-08: la tarea eliminada deja de aparecer
    public function test_tarea_eliminada_deja_de_aparecer_en_el_listado(): void
    {
        $user = User::factory()->create();
        $tarea = Tarea::factory()->create([
            'user_id' => $user->id,
            'titulo' => 'Se va a borrar',
            'fecha_inicio' => '2026-12-10',
            'fecha_limite' => '2026-12-12',
        ]);

        $this->actingAs($user)->delete(route('tareas.destroy', $tarea))->assertRedirect(route('tareas.index'));

        $this->actingAs($user)
            ->get(route('tareas.index', ['mes' => '2026-12']))
            ->assertDontSee('Se va a borrar');
    }
}