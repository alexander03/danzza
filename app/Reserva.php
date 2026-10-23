<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model
{
    use SoftDeletes;
    protected $table = 'reserva';
    protected $dates = ['deleted_at'];

    public function sucursal()
    {
        return $this->belongsTo('App\Sucursal', 'sucursal_id');
    }

    public function horario()
    {
        return $this->belongsTo('App\Horario', 'horario_id')->withTrashed();
    }

    public function cliente()
    {
        return $this->belongsTo('App\Cliente', 'cliente_id');
    }

    /** Rango de fechas al que pertenece (null si es una reserva de un solo día) */
    public function grupo()
    {
        return $this->belongsTo('App\Reservagrupo', 'reservagrupo_id');
    }

}
