@extends('tareas.layout')
@section('titulo', 'Mis tareas')
@section('contenido')
    <style>
        .card { max-width: 1100px; }
        .pagina { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start; }
        @media (max-width: 800px) { .pagina { grid-template-columns: 1fr; } }
        .cabecera { display: flex; justify-content: space-between; align-items: center; }
        table.cal { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 12px; }
        .cal th { font-size: .8rem; color: #666; padding: 6px; }
        .cal td { border: 1px solid #e4e4e7; height: 90px; vertical-align: top; padding: 4px; font-size: .8rem; }
        .cal td.fuera { background: #fafafa; color: #aaa; }
        .cal td.hoy { outline: 2px solid #111; }
        .dia { font-weight: 600; }
        .t { display: block; margin-top: 3px; padding: 2px 4px; border-radius: 4px; background: #e4e4e7; color: #111; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .t.en_progreso { background: #fef9c3; }
        .t.completada { background: #dcfce7; text-decoration: line-through; }
        .panel h2 { font-size: 1rem; margin: 18px 0 0; }
    </style>

    <h1>Mis tareas</h1>

    <div class="pagina">
        <div>
            <div class="cabecera">
                <a href="{{ route('tareas.index', ['mes' => $anterior]) }}">← Anterior</a>
                <strong>{{ $etiquetaMes }}</strong>
                <a href="{{ route('tareas.index', ['mes' => $siguiente]) }}">Siguiente →</a>
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

            <p><a href="{{ route('tareas.index') }}">Ir al mes actual</a></p>
        </div>

        <aside class="panel">
            <a href="{{ route('tareas.create') }}" class="btn">Nueva tarea</a>
            <a href="/privada">Volver</a>

            @if ($total === 0)
                <p>Aún no tienes tareas.</p>
            @else
                <h2>Tareas de {{ $etiquetaMes }}</h2>
                @forelse ($delMes as $tarea)
                    @include('tareas._item', ['tarea' => $tarea])
                @empty
                    <p><small>No hay tareas con fecha en este mes.</small></p>
                @endforelse

                @if ($sinFecha->isNotEmpty())
                    <h2>Sin fecha</h2>
                    @foreach ($sinFecha as $tarea)
                        @include('tareas._item', ['tarea' => $tarea])
                    @endforeach
                @endif
            @endif
        </aside>
    </div>
@endsection