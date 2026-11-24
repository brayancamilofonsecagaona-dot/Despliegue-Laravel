@extends('tareas.layout')
@section('titulo', 'Calendario')
@section('contenido')
    <style>
        .card { max-width: 900px; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; }
        table.cal { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 12px; }
        .cal th { font-size: .8rem; color: #666; padding: 6px; }
        .cal td { border: 1px solid #e4e4e7; height: 96px; vertical-align: top; padding: 4px; font-size: .8rem; }
        .cal td.fuera { background: #fafafa; color: #aaa; }
        .cal td.hoy { outline: 2px solid #111; }
        .dia { font-weight: 600; }
        .t { display: block; margin-top: 3px; padding: 2px 4px; border-radius: 4px; background: #e4e4e7; color: #111; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .t.en_progreso { background: #fef9c3; }
        .t.completada { background: #dcfce7; text-decoration: line-through; }
    </style>

    <h1>Calendario de tareas</h1>

    <div class="cabecera">
        <a href="{{ route('tareas.calendario', ['mes' => $anterior]) }}">← Anterior</a>
        <strong>{{ ucfirst($mes->copy()->locale('es')->translatedFormat('F Y')) }}</strong>
        <a href="{{ route('tareas.calendario', ['mes' => $siguiente]) }}">Siguiente →</a>
    </div>

    <table class="cal">
        <thead>
            <tr>
                @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $nombre)
                    <th>{{ $nombre }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($semanas as $semana)
                <tr>
                    @foreach ($semana as $dia)
                        <td class="{{ $dia['delMes'] ? '' : 'fuera' }} {{ $dia['fecha']->isToday() ? 'hoy' : '' }}">
                            <span class="dia">{{ $dia['fecha']->day }}</span>
                            @foreach ($dia['tareas'] as $tarea)
                                <a class="t {{ $tarea->estado }}" href="{{ route('tareas.edit', $tarea) }}" title="{{ $tarea->titulo }}">{{ $tarea->titulo }}</a>
                            @endforeach
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($sinFecha > 0)
        <p><small>{{ $sinFecha }} tarea(s) sin fecha límite no aparecen en el calendario.</small></p>
    @endif

    <p>
        <a href="{{ route('tareas.index') }}">Ver como lista</a> ·
        <a href="{{ route('tareas.calendario') }}">Ir al mes actual</a>
    </p>
@endsection