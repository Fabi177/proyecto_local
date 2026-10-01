<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // <-- AÑADIDA ESTA LÍNEA

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // <-- AÑADIDA ESTA LÍNEA (DEL PASO 2)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define la relación: Un Usuario tiene (o puede tener) un Comercio.
     * * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function comercio(): HasOne
    {
        return $this->hasOne(Comercio::class);
    }

    /**
     * Define la relación: Un Usuario (comerciante) puede tener VARIOS Comercios.
     * (La columna comercios.user_id no es única, así que no hace falta migración.)
     */
    public function comercios(): HasMany
    {
        return $this->hasMany(Comercio::class);
    }

    /**
     * ¿Es administrador? (users.role = 'admin'). El rol solo se asigna desde la consola:
     * php artisan admin:promover correo@ejemplo.com
     */
    public function esAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
