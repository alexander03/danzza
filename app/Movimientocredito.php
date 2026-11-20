<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Movimiento de créditos de un cliente (historial). Todo cambio de saldo pasa por aquí:
 * descontar/devolver (atómicos) o registrar (cuando el saldo se fija directamente).
 * tipo: PLAN, RESERVA, CANCELACION, CONGELAMIENTO, AJUSTE
 */
class Movimientocredito extends Model
{
    protected $table = 'movimientocredito';

    public function cliente()
    {
        return $this->belongsTo('App\Cliente', 'cliente_id');
    }

    public function usuario()
    {
        return $this->belongsTo('App\User', 'usuario_id')->withTrashed();
    }

    /**
     * Descuenta créditos solo si el cliente los tiene (evita saldo negativo con reservas simultáneas).
     * Devuelve el movimiento registrado, o null si no alcanzaban los créditos.
     */
    public static function descontar($clienteId, $cantidad, $tipo, $detalle = null, $reservaId = null)
    {
        $ok = Cliente::where('id', '=', $clienteId)
            ->where('creditos', '>=', $cantidad)
            ->decrement('creditos', $cantidad) > 0;
        if (!$ok) {
            return null;
        }
        $despues = (int) Cliente::where('id', '=', $clienteId)->value('creditos');
        return self::registrar($clienteId, $despues + $cantidad, $despues, $tipo, $detalle, array('reserva_id' => $reservaId));
    }

    /** Devuelve créditos al cliente y registra el movimiento (nada si la cantidad es 0) */
    public static function devolver($clienteId, $cantidad, $tipo, $detalle = null, $reservaId = null)
    {
        if ($cantidad <= 0) {
            return null;
        }
        Cliente::where('id', '=', $clienteId)->increment('creditos', $cantidad);
        $despues = (int) Cliente::where('id', '=', $clienteId)->value('creditos');
        return self::registrar($clienteId, $despues - $cantidad, $despues, $tipo, $detalle, array('reserva_id' => $reservaId));
    }

    /** Registra un cambio de saldo ya aplicado (de $antes a $despues). Nada si no cambió */
    public static function registrar($clienteId, $antes, $despues, $tipo, $detalle = null, $extra = array())
    {
        $antes   = (int) $antes;
        $despues = (int) $despues;
        if ($antes === $despues && $tipo != 'PLAN') {
            return null;
        }
        $mov = new self();
        $mov->cliente_id    = $clienteId;
        $mov->tipo          = $tipo;
        $mov->cantidad      = $despues - $antes;
        $mov->saldoanterior = $antes;
        $mov->saldonuevo    = $despues;
        $mov->reserva_id    = isset($extra['reserva_id']) ? $extra['reserva_id'] : null;
        $mov->planpago_id   = isset($extra['planpago_id']) ? $extra['planpago_id'] : null;
        $mov->usuario_id    = auth()->check() ? auth()->id() : null;
        $mov->detalle       = $detalle ? mb_substr($detalle, 0, 255) : null;
        $mov->save();
        return $mov;
    }
}
