<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'clave',
        'valor',
    ];

    /**
     * Obtiene el valor de una configuración por su clave.
     */
    public static function get(string $clave, mixed $default = null): mixed
    {
        $item = static::where('clave', $clave)->first();

        return $item ? $item->valor : $default;
    }

    /**
     * Guarda o actualiza el valor de una configuración por su clave.
     */
    public static function set(string $clave, mixed $valor): self
    {
        return static::updateOrCreate(
            ['clave' => $clave],
            ['valor' => (string) $valor]
        );
    }
}
