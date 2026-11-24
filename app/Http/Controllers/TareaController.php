<?php

namespace App\Http\Controllers;

use App\Http\Requests\TareaRequest;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TareaController extends Controller
{
    public function index(Request $request)
    {
        $tareas = $request->user()->tareas()->latest()->get();

        return view('tareas.index', compact('tareas'));
    }

    public function create()
    {
        return view('tareas.create');
    }

    public function store(TareaRequest $request)
    {
        $request->user()->tareas()->create($request->validated());

        return redirect()->route('tareas.index')->with('status', 'Tarea creada con éxito.');
    }

    public function edit(Tarea $tarea)
    {
        $this->autorizar($tarea);

        return view('tareas.edit', compact('tarea'));
    }

    public function update(TareaRequest $request, Tarea $tarea)
    {
        $this->autorizar($tarea);
        $tarea->update($request->validated());

        return redirect()->route('tareas.index')->with('status', 'Tarea actualizada con éxito.');
    }

    public function destroy(Tarea $tarea)
    {
        $this->autorizar($tarea);
        $tarea->delete();

        return redirect()->route('tareas.index')->with('status', 'Tarea eliminada con éxito.');
    }

        public function calendario(Request $request)
    {
        $mes = $this->mesSolicitado($request->query('mes'));

        $tareas = $request->user()->tareas()
            ->whereBetween('fecha_limite', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->orderBy('titulo')
            ->get()
            ->groupBy(fn ($tarea) => $tarea->fecha_limite->toDateString());

        $dia = $mes->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $fin = $mes->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $semanas = [];
        while ($dia->lte($fin)) {
            $semana = [];
            for ($i = 0; $i < 7; $i++) {
                $semana[] = [
                    'fecha' => $dia->copy(),
                    'delMes' => $dia->month === $mes->month,
                    'tareas' => $tareas->get($dia->toDateString(), collect()),
                ];
                $dia->addDay();
            }
            $semanas[] = $semana;
        }

        return view('tareas.calendario', [
            'mes' => $mes,
            'semanas' => $semanas,
            'anterior' => $mes->copy()->subMonth()->format('Y-m'),
            'siguiente' => $mes->copy()->addMonth()->format('Y-m'),
            'sinFecha' => $request->user()->tareas()->whereNull('fecha_limite')->count(),
        ]);
    }

    // Acepta solo meses con formato AAAA-MM; si no es válido, usa el mes actual
    private function mesSolicitado(?string $valor): Carbon
    {
        if ($valor && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $valor)) {
            return Carbon::createFromFormat('Y-m-d', $valor . '-01')->startOfDay();
        }

        return now()->startOfMonth();
    }

    // Solo el dueño puede ver, editar o borrar su tarea
    private function autorizar(Tarea $tarea): void
    {
        abort_if($tarea->user_id !== auth()->id(), 403);
    }
}