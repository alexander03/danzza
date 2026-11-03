<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
	 use SoftDeletes;
    protected $table = 'plan';

    public function clase()
    {
        return $this->belongsTo('App\Clase', 'clase_id');
    }

    /** Ids de las clases que se pueden reservar con el plan, o null si son todas */
    public function clasesPermitidas()
    {
        if (is_null($this->clasespermitidas) || $this->clasespermitidas === '') {
            return null;
        }
        return array_map('intval', explode(',', $this->clasespermitidas));
    }

    public function permiteClase($claseId)
    {
        $permitidas = $this->clasesPermitidas();
        return is_null($permitidas) || in_array((int) $claseId, $permitidas);
    }

    /** Créditos que consume una clase con este plan */
    public function creditosClase($claseId)
    {
        if ($this->claselimite_id && $this->claselimite_id == $claseId && $this->creditosclaselimite > 0) {
            return (int) $this->creditosclaselimite;
        }
        return 1;
    }

    /** Créditos que consume el horario para el cliente (1 si no tiene plan) */
    public static function creditosReserva($cliente, $horario)
    {
        return $cliente->plan ? $cliente->plan->creditosClase($horario->clase_id) : 1;
    }

    /**
     * Primer y último día del periodo en que se cuenta el máximo de clases para esa fecha:
     * el mes calendario, o bloques de 30 días desde el inicio del plan (30DIAS).
     * Sin pago validado no hay fecha de inicio y se usa el mes calendario.
     */
    private function periodoLimite($cliente, $fecha)
    {
        $dia  = Carbon::parse($fecha)->startOfDay();
        $pago = $this->periodolimite == '30DIAS' ? Congelamiento::pagoVigente($cliente) : null;
        if ($pago && $pago->fechainicio) {
            $inicioPlan = Carbon::parse($pago->fechainicio)->startOfDay();
            $bloque     = (int) floor($inicioPlan->diffInDays($dia, false) / 30);
            $inicio     = $inicioPlan->copy()->addDays($bloque * 30);
            return array($inicio->format('Y-m-d'), $inicio->copy()->addDays(29)->format('Y-m-d'));
        }
        return array($dia->copy()->startOfMonth()->format('Y-m-d'), $dia->copy()->endOfMonth()->format('Y-m-d'));
    }

    /**
     * Motivo por el que el plan del cliente no le permite reservar el horario en esas fechas, o null si puede:
     * la clase no está incluida en el plan o se supera el máximo de clases del periodo.
     * $exceptoId = reserva que se modifica (no cuenta contra sí misma).
     */
    public static function bloqueoReserva($cliente, $horario, $fechas, $exceptoId = null)
    {
        $plan = $cliente->plan;
        if (!$plan) {
            return null;
        }
        $clase = Clase::withTrashed()->find($horario->clase_id);
        if (!$plan->permiteClase($horario->clase_id)) {
            return 'El plan ' . $plan->nombre . ' no incluye clases de ' . ($clase ? $clase->nombre : 'este tipo') . '.';
        }
        if (!$plan->limiteclases || $plan->claselimite_id != $horario->clase_id) {
            return null;
        }

        // Las fechas nuevas se agrupan por periodo: en un rango pueden caer en más de uno
        $nuevas = array();
        foreach ($fechas as $fecha) {
            $periodo = $plan->periodoLimite($cliente, $fecha);
            $clave   = implode('|', $periodo);
            $nuevas[$clave] = isset($nuevas[$clave]) ? $nuevas[$clave] + 1 : 1;
        }
        foreach ($nuevas as $clave => $cantidad) {
            list($desde, $hasta) = explode('|', $clave);
            $reservadas = Reserva::join('horario', 'reserva.horario_id', '=', 'horario.id')
                ->where('reserva.cliente_id', '=', $cliente->id)
                ->where('horario.clase_id', '=', $plan->claselimite_id)
                ->whereBetween('reserva.fecha', array($desde, $hasta))
                ->whereNull('reserva.deleted_at');
            if ($exceptoId) {
                $reservadas->where('reserva.id', '<>', $exceptoId);
            }
            if ($reservadas->count() + $cantidad > $plan->limiteclases) {
                return 'El plan ' . $plan->nombre . ' permite máximo ' . $plan->limiteclases . ' clases de ' . $clase->nombre
                    . ' del ' . Carbon::parse($desde)->format('d/m/Y') . ' al ' . Carbon::parse($hasta)->format('d/m/Y') . '.';
            }
        }
        return null;
    }
}
