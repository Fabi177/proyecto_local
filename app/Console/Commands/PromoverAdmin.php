<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Único camino para crear administradores: se hace a mano, desde la consola.
 * Desde la web nadie puede volverse admin (el registro solo acepta usuario y comerciante).
 *
 * Uso:
 *   php artisan admin:promover correo@ejemplo.com            (la cuenta pasa a ser admin)
 *   php artisan admin:promover correo@ejemplo.com --quitar   (la cuenta vuelve a ser "usuario")
 */
class PromoverAdmin extends Command
{
    protected $signature = 'admin:promover
                            {email : Correo de una cuenta que ya existe}
                            {--quitar : Le saca el rol de administrador (pasa a "usuario")}';

    protected $description = 'Convierte una cuenta existente en administrador (o le quita ese rol)';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $usuario = User::where('email', $email)->first();

        if (! $usuario) {
            $this->error("No existe ninguna cuenta con el correo {$email}.");

            return self::FAILURE;
        }

        if ($this->option('quitar')) {
            if (! $usuario->esAdmin()) {
                $this->info("{$email} no es administrador. No se cambió nada.");

                return self::SUCCESS;
            }

            $usuario->role = 'usuario';
            $usuario->save();
            $this->info("{$email} ya no es administrador (ahora es \"usuario\").");

            return self::SUCCESS;
        }

        if ($usuario->esAdmin()) {
            $this->info("{$email} ya es administrador. No se cambió nada.");

            return self::SUCCESS;
        }

        $usuario->role = 'admin';
        $usuario->save();
        $this->info("{$email} ahora es administrador.");

        return self::SUCCESS;
    }
}
