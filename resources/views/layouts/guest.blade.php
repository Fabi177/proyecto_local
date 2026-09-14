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
                --text-green-input: #2ecc71; /* Color del texto en el input */
            }

            /* Fondo con degradado y formas orgánicas (simuladas) */
            .login-background {
                background: linear-gradient(135deg, var(--light-blue) 0%, var(--primary-green) 100%);
                position: relative;
                overflow: hidden;
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
                z-index: 1;
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

            /* === ESTE ES EL NUEVO CÓDIGO PARA LOS INPUTS === */

            /* Contenedor de cada campo (label, input, error) */
            .input-wrapper {
                position: relative;
                margin-top: 2rem; /* Espacio entre campos */
            }

            /* La etiqueta (ej: "Email *") */
            .input-label {
                font-size: 0.875rem; /* 14px */
                color: #555;
                font-weight: 500;
                margin-bottom: 0.5rem; /* Espacio entre label y línea */
            }

            /* Contenedor del icono y el input */
            .input-field-container {
                display: flex;
                align-items: center;
                border-bottom: 2px solid #ccc; /* La línea de abajo */
                padding-bottom: 0.5rem;
                transition: border-color 0.3s ease;
            }

            /* La línea se pone verde al enfocar */
            .input-field-container:focus-within {
                border-color: var(--primary-green);
            }

            /* Icono */
            .input-icon {
                color: var(--light-blue);
                margin-right: 0.75rem;
                flex-shrink: 0;
            }

            /* El campo de texto real */
            .input-field {
                border: none !important;
                background: transparent !important;
                outline: none !important;
                box-shadow: none !important; /* Quita sombras de focus */
                flex-grow: 1;
                font-size: 1rem;
                padding-left: 0 !important; /* Resetea padding */
                color: var(--text-green-input) !important; /* Texto verde */
            }
            .input-field::placeholder {
                color: #aaa;
            }
        </style>
        </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 login-background">

            {{-- Quitamos el logo --}}

            <div class="relative w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg z-10">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
