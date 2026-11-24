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
        $mes       = $this->mesSolicitado($request->query('mes'));
        $inicioMes = $mes->copy()->startOfMonth();
        $finMes    = $mes->copy()->endOfMonth();
        $usuario   = $request->user();

        // Tareas cuyo rango [inicio, fin] toca el mes visible
        $delMes = $usuario->tareas()
            ->whereNotNull('fecha_limite')
            ->whereDate('fecha_limite', '>=', $inicioMes->toDateString())
            ->where(function ($q) use ($finMes) {
                $q->whereDate('fecha_inicio', '<=', $finMes->toDateString())
                  ->orWhere(function ($q2) use ($finMes) {
                      $q2->whereNull('fecha_inicio')
                         ->whereDate('fecha_limite', '<=', $finMes->toDateString());
                  });
            })
            ->orderBy('fecha_limite')
            ->orderBy('titulo')
            ->get();

        $sinFecha = $usuario->tareas()->whereNull('fecha_limite')->latest()->get();

        // Cada tarea se coloca en todos los días de su rango dentro del mes
        $mapa = [];
        foreach ($delMes as $tarea) {
            $desde = ($tarea->fecha_inicio ?? $tarea->fecha_limite)->copy()->startOfDay()->max($inicioMes);
            $hasta = $tarea->fecha_limite->copy()->startOfDay()->min($finMes->copy()->startOfDay());

            for ($d = $desde->copy(); $d->lte($hasta); $d->addDay()) {
                $mapa[$d->toDateString()][] = $tarea;
            }
        }
        $porDia = collect($mapa)->map(fn ($lista) => collect($lista));

        // Cuadrícula de semanas (lunes a domingo)
        $semanas = [];
        $dia     = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);
        $ultimo  = $finMes->copy()->endOfWeek(Carbon::SUNDAY);

        while ($dia->lte($ultimo)) {
            $semana = [];
            for ($i = 0; $i < 7; $i++) {
                $semana[] = [
                    'fecha'  => $dia->copy(),
                    'delMes' => $dia->month === $mes->month,
                    'tareas' => $porDia->get($dia->toDateString(), collect()),
                ];
                $dia->addDay();
            }
            $semanas[] = $semana;
        }

        return view('tareas.index', [
            'etiquetaMes' => ucfirst($mes->copy()->locale('es')->translatedFormat('F Y')),
            'semanas'     => $semanas,
            'delMes'      => $delMes,
            'sinFecha'    => $sinFecha,
            'total'       => $usuario->tareas()->count(),
            'anterior'    => $mes->copy()->subMonth()->format('Y-m'),
            'siguiente'   => $mes->copy()->addMonth()->format('Y-m'),
        ]);
    }

    public function calendario(Request $request)
    {
        return $this->index($request);
    }

    public function create()
    {
        return view('tareas.create', ['tarea' => null]);
    }

    public function store(TareaRequest $request)
    {
        $request->user()->tareas()->create($request->validated());

        return redirect()->route('tareas.index')->with('status', 'Tarea creada con éxito.');
    }

    public function show(Tarea $tarea)
    {
        $this->autorizar($tarea);

        return view('tareas.show', compact('tarea'));
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

    private function autorizar(Tarea $tarea): void
    {
        abort_if($tarea->user_id !== auth()->id(), 403);
    }

    private function mesSolicitado(?string $mes): Carbon
    {
        if ($mes !== null && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            return Carbon::createFromFormat('!Y-m', $mes);
        }

        return now()->startOfMonth();
    }
}