<?php

namespace App\Http\Controllers;

use App\Http\Requests\TareaRequest;
use App\Models\Tarea;
use Illuminate\Http\Request;

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

    // Solo el dueño puede ver, editar o borrar su tarea
    private function autorizar(Tarea $tarea): void
    {
        abort_if($tarea->user_id !== auth()->id(), 403);
    }
}