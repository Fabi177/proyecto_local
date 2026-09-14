<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Panel Comerciante</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --primary-green: #2ecc71;
                --light-blue: #3498db;
                --text-dark: #34495e;
                --comerciante-bg: #1f2937; /* Color de sidebar (gris oscuro) */
                --comerciante-hover: #374151; /* Hover de sidebar */
            }

            /* Estilos de formulario copiados de guest.blade.php
               para que 'comercianto.create' funcione.
            */
            .input-wrapper {
                position: relative;
                margin-top: 1rem;
            }
            .input-label {
                font-size: 0.875rem; /* 14px */
                color: #555;
                font-weight: 500;
                margin-bottom: 0.5rem;
            }
            .input-field-container {
                display: flex;
                align-items: center;
                border-bottom: 2px solid #ccc;
                padding-bottom: 0.5rem;
                transition: border-color 0.3s ease;
            }
            .input-field-container:focus-within {
                border-color: var(--primary-green);
            }
            .input-icon {
                color: var(--light-blue);
                margin-right: 0.75rem;
                flex-shrink: 0;
            }
            .input-field {
                border: none !important;
                background: transparent !important;
                outline: none !important;
                box-shadow: none !important;
                flex-grow: 1;
                font-size: 1rem;
                padding-left: 0 !important;
                color: var(--text-dark) !important;
                width: 100%;
            }
            .input-field::placeholder { color: #aaa; }
            .checkbox-wrapper {
                display: flex;
                align-items: center;
                margin-top: 1rem;
            }
            .checkbox-input {
                width: 1.25rem;
                height: 1.25rem;
                border-radius: 0.25rem;
                border-color: #ccc;
                color: var(--primary-green);
                transition: all 0.2s ease;
            }
            .checkbox-input:checked {
                border-color: var(--primary-green);
                background-color: var(--primary-green);
            }
            .checkbox-label {
                margin-left: 0.5rem;
                font-size: 0.875rem;
                color: #333;
            }
        </style>
    </head>
    <body class="font-sans antialiased">

        <div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-100">

            <div :class="sidebarOpen ? 'block' : 'hidden'" @click="sidebarOpen = false" class="fixed inset-0 z-20 transition-opacity bg-black opacity-50 lg:hidden"></div>

            <div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'" class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition duration-300 transform bg-[var(--comerciante-bg)] lg:translate-x-0 lg:static lg:inset-0">

                <div class="flex items-center justify-center mt-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                        <span class="text-white text-2xl font-bold ml-2">LocalCommers</span>
                    </a>
                </div>

                <nav class="mt-10">
                    <a class="flex items-center px-6 py-2 mt-4 text-gray-100 {{ request()->routeIs('dashboard') ? 'bg-[var(--comerciante-hover)]' : 'text-gray-400' }} hover:bg-[var(--comerciante-hover)] hover:text-gray-100"
                       href="{{ route('dashboard') }}">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v18M6.75 6.75h10.5M6.75 11.25h10.5M6.75 15.75h10.5M20.25 3v18" /></svg>
                        <span class="mx-3">Mi Panel</span>
                    </a>

                    <a class="flex items-center px-6 py-2 mt-4 {{ request()->routeIs('comercio.edit') ? 'bg-[var(--comerciante-hover)] text-gray-100' : 'text-gray-400' }} hover:bg-[var(--comerciante-hover)] hover:text-gray-100"
                       href="{{ route('comercio.edit') }}">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                        <span class="mx-3">Mi Comercio</span>
                    </a>

                    <a class="flex items-center px-6 py-2 mt-4 {{ request()->routeIs('profile.edit') ? 'bg-[var(--comerciante-hover)] text-gray-100' : 'text-gray-400' }} hover:bg-[var(--comerciante-hover)] hover:text-gray-100"
                       href="{{ route('profile.edit') }}">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        <span class="mx-3">Mi Perfil</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <a class="flex items-center px-6 py-2 text-gray-400 hover:bg-[var(--comerciante-hover)] hover:text-gray-100"
                           href="{{ route('logout') }}"
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3H7.5A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H5.25" /></svg>
                            <span class="mx-3">Cerrar Sesión</span>
                        </a>
                    </form>
                </nav>
            </div>

            <div class="flex-1 flex flex-col overflow-hidden">

                <header class="flex items-center justify-between p-6 bg-white border-b shadow-md">
                    <button @click.stop="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none lg:hidden">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="flex-1 text-right">
                        <span class="text-gray-600">Hola, <span class="font-medium">{{ Auth::user()->name }}</span></span>
                    </div>
                </header>

                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                    <div class="py-12">
                        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

                            @isset($header)
                                <header class="mb-6 bg-white shadow sm:rounded-lg">
                                    <div class="py-6 px-4 sm:px-6 lg:px-8">
                                        {{ $header }}
                                    </div>
                                </header>
                            @endisset


                            {{ $slot }}

                        </div>
                    </div>
                </main>
             </div>
        </div>
    </body>
</html>
