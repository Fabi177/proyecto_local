@if (Auth::user()->role === 'comerciante')

<!-- ======================================================= -->
<!-- SI ES COMERCIANTE, CARGA EL LAYOUT DEL PANEL DE CONTROL -->
<!-- ======================================================= -->
<x-comerciante-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Comerciante') }}
        </h2>
    </x-slot>

    @include('dashboard.comerciante')

</x-comerciante-layout>


@else

<!-- ======================================================= -->
<!-- SI ES USUARIO, CARGA EL LAYOUT PÚBLICO (CON GRADIENTE) -->
<!-- ======================================================= -->
<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('¡Hola, ') . Auth::user()->name . '!' }}
        </h2>
    </x-slot>

    @include('dashboard.usuario')

</x-app-layout>


@endif
