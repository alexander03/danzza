<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Reserva por rango de fechas: agrupa una reserva (tabla reserva) por cada día
 * del rango. Se paga una sola vez por todo el rango.
 */
class Reservagrupo extends Model
{
    use SoftDeletes;
    protected $table = 'reservagrupo';
    protected $dates = ['deleted_at'];

    public function reservas()
    {
        return $this->hasMany('App\Reserva', 'reservagrupo_id');
    }

    public function cliente()
    {
        return $this->belongsTo('App\Cliente', 'cliente_id');
    }

    public function horario()
    {
        return $this->belongsTo('App\Horario', 'horario_id');
    }

    public function sucursal()
    {
        return $this->belongsTo('App\Sucursal', 'sucursal_id');
    }
}
