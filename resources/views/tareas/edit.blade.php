@extends('tareas.layout')
@section('titulo', 'Editar tarea')
@section('contenido')
    <h1>Editar tarea</h1>
    <form method="POST" action="{{ route('tareas.update', $tarea) }}" novalidate>
        @method('PUT')
        @include('tareas._form', ['tarea' => $tarea])
        <button type="submit">Actualizar</button>
    </form>
    <p><a href="{{ route('tareas.index') }}">Cancelar</a></p>
@endsection