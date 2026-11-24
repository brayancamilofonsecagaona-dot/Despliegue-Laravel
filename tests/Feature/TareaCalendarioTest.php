<?php

namespace Tests\Feature;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TareaCalendarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_es_redirigido_al_login(): void
    {
        $this->get(route('tareas.calendario'))->assertRedirect('/login');
    }

    public function test_muestra_el_mes_y_las_tareas_de_ese_mes(): void
    {
        $yo = User::factory()->create();
        Tarea::factory()->for($yo)->create(['titulo' => 'Entrega final', 'fecha_limite' => '2026-12-15']);

        $this->actingAs($yo)->get(route('tareas.calendario', ['mes' => '2026-12']))
            ->assertOk()
            ->assertSee('Diciembre 2026')
            ->assertSee('Entrega final');
    }

    public function test_incluye_el_primer_y_el_ultimo_dia_del_mes(): void
    {
        $yo = User::factory()->create();
        Tarea::factory()->for($yo)->create(['titulo' => 'Primer dia', 'fecha_limite' => '2026-12-01']);
        Tarea::factory()->for($yo)->create(['titulo' => 'Ultimo dia', 'fecha_limite' => '2026-12-31']);

        $this->actingAs($yo)->get(route('tareas.calendario', ['mes' => '2026-12']))
            ->assertSee('Primer dia')
            ->assertSee('Ultimo dia');
    }

    public function test_no_muestra_tareas_de_otros_meses(): void
    {
        $yo = User::factory()->create();
        Tarea::factory()->for($yo)->create(['titulo' => 'Tarea de enero', 'fecha_limite' => '2027-01-10']);

        $this->actingAs($yo)->get(route('tareas.calendario', ['mes' => '2026-12']))
            ->assertOk()
            ->assertDontSee('Tarea de enero');
    }

    public function test_no_muestra_tareas_de_otros_usuarios(): void
    {
        $yo = User::factory()->create();
        Tarea::factory()->create(['titulo' => 'Tarea ajena', 'fecha_limite' => '2026-12-15']);

        $this->actingAs($yo)->get(route('tareas.calendario', ['mes' => '2026-12']))
            ->assertOk()
            ->assertDontSee('Tarea ajena');
    }

    public function test_las_tareas_sin_fecha_aparecen_en_el_panel_lateral(): void
    {
        $yo = User::factory()->create();
        Tarea::factory()->for($yo)->create(['titulo' => 'Sin fecha uno', 'fecha_limite' => null]);

        $this->actingAs($yo)->get(route('tareas.calendario', ['mes' => '2026-12']))
            ->assertSee('Sin fecha uno');
    }

    public function test_la_pagina_de_tareas_muestra_calendario_y_panel_a_la_vez(): void
    {
        $yo = User::factory()->create();
        Tarea::factory()->for($yo)->create([
            'titulo' => 'Tarea del mes',
            'fecha_limite' => now()->startOfMonth()->addDays(2),
        ]);

        $this->actingAs($yo)->get(route('tareas.index'))
            ->assertOk()
            ->assertSee(ucfirst(now()->locale('es')->translatedFormat('F Y')))
            ->assertSee('Lun')
            ->assertSee('Nueva tarea')
            ->assertSee('Tarea del mes');
    }

    public function test_un_mes_invalido_usa_el_mes_actual(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('tareas.calendario', ['mes' => 'abc']))
            ->assertOk()
            ->assertSee(ucfirst(now()->locale('es')->translatedFormat('F Y')));
    }

    public function test_los_enlaces_de_navegacion_apuntan_al_mes_anterior_y_siguiente(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('tareas.calendario', ['mes' => '2026-12']))
            ->assertSee('mes=2026-11', false)
            ->assertSee('mes=2027-01', false);
    }
}