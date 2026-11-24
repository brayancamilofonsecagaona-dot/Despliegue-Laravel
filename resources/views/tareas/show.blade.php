@extends('tareas.layout')
@section('titulo', 'Detalle de la tarea')
@section('contenido')
    <h1>{{ $tarea->titulo }}</h1>

    <p><strong>Descripción:</strong> {{ $tarea->descripcion ?: 'Sin descripción' }}</p>
    <p><strong>Fecha de inicio:</strong> {{ $tarea->fecha_inicio?->format('d/m/Y') ?? '—' }}</p>
    <p><strong>Fecha de fin:</strong> {{ $tarea->fecha_limite?->format('d/m/Y') ?? '—' }}</p>
    <p><strong>Estado:</strong> {{ \App\Models\Tarea::ESTADOS[$tarea->estado] ?? $tarea->estado }}</p>

    <p>
        <a href="{{ route('tareas.edit', $tarea) }}">Editar</a> ·
        <a href="{{ route('tareas.index') }}">Volver</a>
    </p>
@endsection