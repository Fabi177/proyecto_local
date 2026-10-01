<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resena extends Model
{
    protected $table = 'resenas';

    protected $fillable = ['comercio_id', 'user_id', 'calificacion', 'comentario'];

    protected function casts(): array
    {
        return ['calificacion' => 'integer'];
    }

    public function comercio(): BelongsTo
    {
        return $this->belongsTo(Comercio::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
