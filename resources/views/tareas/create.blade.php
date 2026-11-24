@extends('tareas.layout')
@section('titulo', 'Nueva tarea')
@section('contenido')
    <h1>Nueva tarea</h1>
    <form method="POST" action="{{ route('tareas.store') }}" novalidate>
        @include('tareas._form', ['tarea' => null])
        <button type="submit">Guardar</button>
    </form>
    <p><a href="{{ route('tareas.index') }}">Cancelar</a></p>
@endsection