<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Pago de un plan (primera vez o cambio de plan).
 * situacion: PENDIENTE -> VALIDADO / RECHAZADO.
 * El plan se asigna al cliente recién cuando el pago es VALIDADO.
 */
class Planpago extends Model
{
    use SoftDeletes;
    protected $table = 'planpago';

    public function cliente()
    {
        return $this->belongsTo('App\Cliente', 'cliente_id');
    }

    public function plan()
    {
        return $this->belongsTo('App\Plan', 'plan_id');
    }
}
