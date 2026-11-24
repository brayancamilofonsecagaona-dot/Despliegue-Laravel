<div class="item">
    <strong>{{ $tarea->titulo }}</strong>
    <span class="badge">{{ \App\Models\Tarea::ESTADOS[$tarea->estado] ?? $tarea->estado }}</span>
    @if ($tarea->descripcion)
        <p>{{ $tarea->descripcion }}</p>
    @endif
    @if ($tarea->fecha_limite)
        <small>Vence: {{ $tarea->fecha_limite->format('d/m/Y') }}</small>
    @endif
    <div class="acciones">
        <a href="{{ route('tareas.edit', $tarea) }}">Editar</a>
        <form method="POST" action="{{ route('tareas.destroy', $tarea) }}" onsubmit="return confirm('¿Eliminar esta tarea?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="link">Eliminar</button>
        </form>
    </div>
</div>