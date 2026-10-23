<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Confirmación de asistencia del instructor por horario + fecha.
 * Primero confirma el instructor (INSTRUCTOR CONFIRMADO) y luego el administrador (ADMIN CONFIRMADO).
 */
class Validahorario extends Model
{
    protected $table = 'validahorario';

    public function horario()
    {
        return $this->belongsTo('App\Horario', 'horario_id');
    }

    public function instructor()
    {
        return $this->belongsTo('App\Instructor', 'instructor_id');
    }
}
