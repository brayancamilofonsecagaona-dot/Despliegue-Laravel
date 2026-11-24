@csrf
<label for="titulo">Título</label>
<input id="titulo" type="text" name="titulo" value="{{ old('titulo', $tarea?->titulo) }}">
@error('titulo') <div class="error">{{ $message }}</div> @enderror

<label for="descripcion">Descripción</label>
<textarea id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $tarea?->descripcion) }}</textarea>
@error('descripcion') <div class="error">{{ $message }}</div> @enderror

<label for="estado">Estado</label>
<select id="estado" name="estado">
    @foreach (\App\Models\Tarea::ESTADOS as $valor => $etiqueta)
        <option value="{{ $valor }}" @selected(old('estado', $tarea?->estado ?? 'pendiente') === $valor)>{{ $etiqueta }}</option>
    @endforeach
</select>
@error('estado') <div class="error">{{ $message }}</div> @enderror

<label for="fecha_limite">Fecha límite (opcional)</label>
<input id="fecha_limite" type="date" name="fecha_limite" value="{{ old('fecha_limite', $tarea?->fecha_limite?->format('Y-m-d')) }}">
@error('fecha_limite') <div class="error">{{ $message }}</div> @enderror