{{--
    Calificaciones y comentarios del comercio.

    - Ver el promedio y leer los comentarios es PÚBLICO (también sin sesión).
    - Escribir exige sesión iniciada y cuenta de cliente (users.role = 'usuario').
    - Del autor se muestra solo el nombre, nunca el correo.
    - Los comentarios se imprimen escapados con {{ }} (nunca como HTML).

    Variables: $comercio, $resenas (paginadas), $promedio, $totalResenas, $miResena
--}}
@php
    $usuario = auth()->user();
    $estrellas = fn (float $n) => str_repeat('★', (int) round($n)).str_repeat('☆', 5 - (int) round($n));
@endphp

<section id="resenas" class="mt-8 bg-white shadow-xl sm:rounded-lg p-6 md:p-8" aria-labelledby="resenas-titulo">
    <h2 id="resenas-titulo" class="text-2xl font-bold text-gray-900">Calificaciones y comentarios</h2>

    <!-- Resumen: promedio y cantidad (público) -->
    <div class="mt-3 flex items-center gap-3" data-resumen-resenas>
        @if ($totalResenas > 0)
            <span class="text-4xl font-black text-gray-900">{{ number_format($promedio, 1, ',', '') }}</span>
            <div>
                <p class="text-2xl leading-none text-amber-400" aria-label="Promedio de {{ number_format($promedio, 1, ',', '') }} sobre 5">{{ $estrellas($promedio) }}</p>
                <p class="text-sm text-gray-500">{{ $totalResenas }} {{ $totalResenas === 1 ? 'calificación' : 'calificaciones' }}</p>
            </div>
        @else
            <p class="text-gray-500">Todavía no hay calificaciones. ¡Sé el primero en opinar!</p>
        @endif
    </div>

    @if (session('status_resena'))
        <p class="mt-4 rounded-lg bg-green-100 px-4 py-2 text-sm font-medium text-green-800" role="status">{{ session('status_resena') }}</p>
    @endif

    <!-- Escribir: solo clientes con sesión iniciada -->
    <div class="mt-6 border-t pt-6">
        @guest
            <div class="rounded-lg bg-gray-50 p-4 text-gray-700" data-resena-invitado>
                Para calificar o comentar necesitás una cuenta de cliente.
                <a href="{{ route('login') }}" class="font-semibold text-[var(--light-blue,#3498db)] underline">Ingresá</a>
                o
                <a href="{{ route('register') }}" class="font-semibold text-[var(--primary-green,#2ecc71)] underline">creá tu cuenta gratis</a>.
            </div>
        @else
            @if ($usuario->esCliente())
                <form method="POST" action="{{ route('resenas.store', $comercio) }}" class="space-y-3"
                      x-data="{ nota: {{ (int) old('calificacion', $miResena->calificacion ?? 0) }}, sobre: 0 }" data-resena-form>
                    @csrf
                    <h3 class="text-lg font-semibold text-gray-800">{{ $miResena ? 'Tu calificación' : 'Dejá tu calificación' }}</h3>

                    <div class="flex items-center gap-1" role="group" aria-label="Calificación de 1 a 5 estrellas" @mouseleave="sobre = 0">
                        @foreach (range(1, 5) as $n)
                            <button type="button" @click="nota = {{ $n }}" @mouseenter="sobre = {{ $n }}"
                                    class="text-4xl leading-none transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--light-blue,#3498db)] rounded"
                                    :class="(sobre || nota) >= {{ $n }} ? 'text-amber-400' : 'text-gray-300'"
                                    :aria-pressed="nota === {{ $n }} ? 'true' : 'false'"
                                    aria-label="{{ $n }} {{ $n === 1 ? 'estrella' : 'estrellas' }}">★</button>
                        @endforeach
                        <input type="hidden" name="calificacion" :value="nota">
                    </div>
                    @error('calificacion')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div>
                        <label for="comentario" class="sr-only">Comentario (opcional)</label>
                        <textarea id="comentario" name="comentario" rows="3" maxlength="1000"
                                  placeholder="Contá cómo fue tu experiencia (opcional)"
                                  class="w-full rounded-lg border-gray-300 focus:border-[var(--primary-green,#2ecc71)] focus:ring-[var(--primary-green,#2ecc71)]">{{ old('comentario', $miResena->comentario ?? '') }}</textarea>
                        @error('comentario')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Se muestra tu nombre, nunca tu correo.</p>
                    </div>

                    <button type="submit" class="inline-flex items-center rounded-lg bg-[var(--primary-green,#2ecc71)] px-5 py-2 font-bold text-white shadow transition hover:bg-green-600">
                        {{ $miResena ? 'Actualizar mi calificación' : 'Publicar' }}
                    </button>
                </form>
            @elseif ($usuario->role === 'comerciante')
                <p class="rounded-lg bg-gray-50 p-4 text-gray-600" data-resena-nota>Las calificaciones las dejan los clientes. Desde tu cuenta de comerciante podés leerlas.</p>
            @endif
        @endguest
    </div>

    <!-- Lista de reseñas (público) -->
    @if ($resenas->count())
        <ul class="mt-6 divide-y divide-gray-200 border-t" data-lista-resenas>
            @foreach ($resenas as $resena)
                <li class="py-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $resena->user?->name ?? 'Usuario eliminado' }}</p>
                            <p class="text-sm text-amber-400" aria-label="{{ $resena->calificacion }} de 5 estrellas">{{ $estrellas($resena->calificacion) }}
                                <span class="ml-1 text-gray-400">{{ $resena->created_at->format('d/m/Y') }}</span>
                            </p>
                        </div>

                        @if ($usuario && ($usuario->esAdmin() || $usuario->id === $resena->user_id))
                            <form method="POST" action="{{ route('resenas.destroy', $resena) }}" onsubmit="return confirm('¿Eliminar esta reseña?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-red-600 hover:underline">Eliminar</button>
                            </form>
                        @endif
                    </div>
                    @if ($resena->comentario)
                        <p class="mt-2 whitespace-pre-line text-gray-700">{{ $resena->comentario }}</p>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="mt-4">{{ $resenas->links() }}</div>
    @endif
</section>
