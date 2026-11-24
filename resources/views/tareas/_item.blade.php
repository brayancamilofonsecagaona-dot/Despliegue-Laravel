<div class="item">
    <strong>{{ $tarea->titulo }}</strong>
    <span class="badge">{{ \App\Models\Tarea::ESTADOS[$tarea->estado] ?? $tarea->estado }}</span>
    @if ($tarea->descripcion)
        <p>{{ $tarea->descripcion }}</p>
    @endif
    @if ($tarea->fecha_limite)
        <small>
            @if ($tarea->fecha_inicio)
                Del {{ $tarea->fecha_inicio->format('d/m/Y') }} al {{ $tarea->fecha_limite->format('d/m/Y') }}
            @else
                Vence: {{ $tarea->fecha_limite->format('d/m/Y') }}
            @endif
        </small>
    @endif
    <div class="acciones">
        <a href="{{ route('tareas.show', $tarea) }}">Ver</a>
        <a href="{{ route('tareas.edit', $tarea) }}">Editar</a>
        <form method="POST" action="{{ route('tareas.destroy', $tarea) }}" onsubmit="return confirm('¿Eliminar esta tarea?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="link">Eliminar</button>
        </form>
    </div>
</div>