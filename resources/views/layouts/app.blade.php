<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --primary-green: #2ecc71; /* Verde de la referencia */
                --light-blue: #3498db;   /* Azul-verde de la referencia */
                --text-dark: #34495e;
                --text-green-input: #2ecc71;
            }

            /* Fondo con degradado y formas orgánicas */
            .login-background {
                background: linear-gradient(135deg, var(--light-blue) 0%, var(--primary-green) 100%);
                position: relative;
                overflow: hidden;
                z-index: 0; /* Asegura que sea el fondo */
            }

            /* Formas orgánicas (simulación con CSS) */
            .login-background::before,
            .login-background::after {
                content: '';
                position: absolute;
                background-color: rgba(255, 255, 255, 0.15);
                border-radius: 50%;
                filter: blur(80px);
                opacity: 0.8;
                z-index: -1; /* Detrás del contenido */
            }
            .login-background::before {
                width: 500px;
                height: 500px;
                top: -150px;
                left: -200px;
            }
            .login-background::after {
                width: 600px;
                height: 600px;
                bottom: -200px;
                right: -250px;
            }

            /* Estilos de los formularios */
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
        <div class="min-h-screen login-background">

            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
