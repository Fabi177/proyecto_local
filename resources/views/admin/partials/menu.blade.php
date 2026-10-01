{{-- Pestañas del panel de administración. Recibe $activa: 'index' | 'comercios' | 'usuarios' --}}
@php
    $pestanas = [
        'index' => ['Resumen', route('admin.index')],
        'comercios' => ['Comercios', route('admin.comercios')],
        'usuarios' => ['Usuarios', route('admin.usuarios')],
    ];
@endphp
<nav class="flex flex-wrap gap-2" aria-label="Secciones de administración">
    @foreach ($pestanas as $clave => [$texto, $enlace])
        <a href="{{ $enlace }}"
           @if ($activa === $clave) aria-current="page" @endif
           class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $activa === $clave ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            {{ $texto }}
        </a>
    @endforeach
</nav>
