<?php

namespace Tests\Feature;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TareaCrudTest extends TestCase
{
    use RefreshDatabase;

    private function datos(array $cambios = []): array
    {
        return array_merge([
            'titulo' => 'Estudiar pruebas',
            'descripcion' => 'Repasar PHPUnit',
            'estado' => 'pendiente',
            'fecha_limite' => '2026-12-01',
        ], $cambios);
    }

    public function test_visitante_es_redirigido_al_login_en_todas_las_rutas(): void
    {
        $tarea = Tarea::factory()->create();

        $this->get(route('tareas.index'))->assertRedirect('/login');
        $this->get(route('tareas.create'))->assertRedirect('/login');
        $this->post(route('tareas.store'), $this->datos())->assertRedirect('/login');
        $this->get(route('tareas.edit', $tarea))->assertRedirect('/login');
        $this->put(route('tareas.update', $tarea), $this->datos())->assertRedirect('/login');
        $this->delete(route('tareas.destroy', $tarea))->assertRedirect('/login');
    }

    public function test_el_listado_muestra_solo_las_tareas_del_usuario(): void
    {
        $yo = User::factory()->create();
        $otro = User::factory()->create();
        Tarea::factory()->for($yo)->create(['titulo' => 'Mi tarea']);
        Tarea::factory()->for($otro)->create(['titulo' => 'Tarea ajena']);

        $this->actingAs($yo)->get(route('tareas.index'))
            ->assertOk()
            ->assertSee('Mi tarea')
            ->assertDontSee('Tarea ajena');
    }

    public function test_el_listado_vacio_muestra_un_mensaje(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('tareas.index'))
            ->assertOk()
            ->assertSee('Aún no tienes tareas.');
    }

    public function test_el_formulario_de_creacion_carga(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('tareas.create'))
            ->assertOk()
            ->assertSee('name="titulo"', false);
    }

    public function test_crear_una_tarea_valida(): void
    {
        $yo = User::factory()->create();

        $this->actingAs($yo)->post(route('tareas.store'), $this->datos())
            ->assertRedirect(route('tareas.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('tareas', [
            'user_id' => $yo->id,
            'titulo' => 'Estudiar pruebas',
            'estado' => 'pendiente',
        ]);
    }

    public function test_no_se_puede_crear_una_tarea_a_nombre_de_otro_usuario(): void
    {
        $yo = User::factory()->create();
        $otro = User::factory()->create();

        $this->actingAs($yo)->post(route('tareas.store'), $this->datos(['user_id' => $otro->id]));

        $this->assertDatabaseHas('tareas', ['titulo' => 'Estudiar pruebas', 'user_id' => $yo->id]);
        $this->assertDatabaseCount('tareas', 1);
    }

    public function test_el_titulo_es_obligatorio(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tareas.store'), $this->datos(['titulo' => '']))
            ->assertSessionHasErrors('titulo');

        $this->assertDatabaseCount('tareas', 0);
    }

    public function test_el_titulo_no_puede_superar_255_caracteres(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tareas.store'), $this->datos(['titulo' => str_repeat('a', 256)]))
            ->assertSessionHasErrors('titulo');
    }

    public function test_el_estado_debe_ser_uno_valido(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tareas.store'), $this->datos(['estado' => 'inventado']))
            ->assertSessionHasErrors('estado');
    }

    public function test_la_fecha_limite_debe_ser_una_fecha(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tareas.store'), $this->datos(['fecha_limite' => 'no-es-fecha']))
            ->assertSessionHasErrors('fecha_limite');
    }

    public function test_la_descripcion_y_la_fecha_son_opcionales(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tareas.store'), $this->datos(['descripcion' => null, 'fecha_limite' => null]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('tareas', 1);
    }

    public function test_editar_muestra_el_formulario_con_los_datos(): void
    {
        $yo = User::factory()->create();
        $tarea = Tarea::factory()->for($yo)->create(['titulo' => 'Titulo guardado']);

        $this->actingAs($yo)->get(route('tareas.edit', $tarea))
            ->assertOk()
            ->assertSee('Titulo guardado');
    }

    public function test_actualizar_una_tarea_propia(): void
    {
        $yo = User::factory()->create();
        $tarea = Tarea::factory()->for($yo)->create();

        $this->actingAs($yo)
            ->put(route('tareas.update', $tarea), $this->datos(['titulo' => 'Nuevo titulo', 'estado' => 'completada']))
            ->assertRedirect(route('tareas.index'));

        $this->assertDatabaseHas('tareas', ['id' => $tarea->id, 'titulo' => 'Nuevo titulo', 'estado' => 'completada']);
    }

    public function test_actualizar_con_datos_invalidos_no_cambia_la_tarea(): void
    {
        $yo = User::factory()->create();
        $tarea = Tarea::factory()->for($yo)->create(['titulo' => 'Original']);

        $this->actingAs($yo)
            ->put(route('tareas.update', $tarea), $this->datos(['titulo' => '']))
            ->assertSessionHasErrors('titulo');

        $this->assertDatabaseHas('tareas', ['id' => $tarea->id, 'titulo' => 'Original']);
    }

    public function test_eliminar_una_tarea_propia(): void
    {
        $yo = User::factory()->create();
        $tarea = Tarea::factory()->for($yo)->create();

        $this->actingAs($yo)->delete(route('tareas.destroy', $tarea))
            ->assertRedirect(route('tareas.index'));

        $this->assertDatabaseMissing('tareas', ['id' => $tarea->id]);
    }

    public function test_no_se_puede_editar_la_tarea_de_otro_usuario(): void
    {
        $tarea = Tarea::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('tareas.edit', $tarea))
            ->assertForbidden();
    }

    public function test_no_se_puede_actualizar_la_tarea_de_otro_usuario(): void
    {
        $tarea = Tarea::factory()->create(['titulo' => 'Original']);

        $this->actingAs(User::factory()->create())
            ->put(route('tareas.update', $tarea), $this->datos(['titulo' => 'Hackeada']))
            ->assertForbidden();

        $this->assertDatabaseHas('tareas', ['id' => $tarea->id, 'titulo' => 'Original']);
    }

    public function test_no_se_puede_eliminar_la_tarea_de_otro_usuario(): void
    {
        $tarea = Tarea::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('tareas.destroy', $tarea))
            ->assertForbidden();

        $this->assertDatabaseHas('tareas', ['id' => $tarea->id]);
    }

    public function test_al_eliminar_un_usuario_se_eliminan_sus_tareas(): void
    {
        $yo = User::factory()->create();
        Tarea::factory()->for($yo)->count(2)->create();

        $yo->delete();

        $this->assertDatabaseCount('tareas', 0);
    }
}