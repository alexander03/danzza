<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Congelamiento del plan de un cliente. Lo inicia y lo termina el administrador.
 * Al descongelar, los días congelados se suman a la fecha de vencimiento del plan (planpago).
 * situacion: ACTUAL (sigue congelado) / FINALIZADO
 */
class Congelamiento extends Model
{
    protected $table = 'congelamiento';

    public function cliente()
    {
        return $this->belongsTo('App\Cliente', 'cliente_id');
    }

    public function planpago()
    {
        return $this->belongsTo('App\Planpago', 'planpago_id');
    }

    /** Congelamiento en curso del cliente, o null */
    public static function actual($clienteId)
    {
        return self::where('cliente_id', '=', $clienteId)
            ->where('situacion', '=', 'ACTUAL')
            ->orderBy('id', 'desc')
            ->first();
    }

    /** Último pago validado del plan actual del cliente, o null */
    public static function pagoVigente($cliente)
    {
        return Planpago::where('cliente_id', '=', $cliente->id)
            ->where('plan_id', '=', $cliente->plan_id)
            ->where('situacion', '=', 'VALIDADO')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Motivo por el que el cliente no puede reservar en esa fecha, o null si puede:
     * plan congelado (desde el inicio del congelamiento) o fecha posterior al vencimiento del plan.
     */
    public static function bloqueoReserva($cliente, $fecha)
    {
        $dia = Carbon::parse($fecha)->format('Y-m-d');

        $congelamiento = self::actual($cliente->id);
        if ($congelamiento && $dia >= Carbon::parse($congelamiento->fechainicio)->format('Y-m-d')) {
            return 'El plan está congelado desde el ' . Carbon::parse($congelamiento->fechainicio)->format('d/m/Y') . '.';
        }

        $pago = self::pagoVigente($cliente);
        if ($pago && $pago->fechavencimiento && $dia > Carbon::parse($pago->fechavencimiento)->format('Y-m-d')) {
            return 'El plan vence el ' . Carbon::parse($pago->fechavencimiento)->format('d/m/Y') . ' y no cubre el ' . Carbon::parse($dia)->format('d/m/Y') . '.';
        }

        return null;
    }
}
