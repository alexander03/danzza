<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;
    protected $table = 'cliente';

    /** Estados del plan que se calculan solos (filtro "Situación" del listado) */
    const ESTADOS_PLAN = array('SIN PLAN', 'PLAN VENCIDO', 'SIN CLASES');

    public function plan()
    {
        return $this->belongsTo('App\Plan', 'plan_id');
    }

    public function reservas()
    {
        return $this->hasMany('App\Reserva', 'cliente_id');
    }

    public function sucursal()
    {
        return $this->belongsTo('App\Sucursal', 'sucursal_id');
    }

    /**
     * Estado del plan: 'SIN PLAN' (no compró o su plan fue eliminado), 'PLAN VENCIDO' (pasó la fecha de
     * vencimiento del último pago validado), 'SIN CLASES' (sin créditos y aún no vence) o null.
     * Un plan congelado no cuenta como vencido ni sin clases. Misma regla que scopeEstadoPlan().
     */
    public function estadoPlan()
    {
        if (!($this->plan_id > 0) || !$this->plan) {
            return 'SIN PLAN';
        }
        if (Congelamiento::actual($this->id)) {
            return null;
        }
        $pago = Planpago::where('cliente_id', '=', $this->id)
            ->where('plan_id', '=', $this->plan_id)
            ->where('situacion', '=', 'VALIDADO')
            ->orderBy('id', 'desc')
            ->first();
        if ($pago && $pago->fechavencimiento && Carbon::parse($pago->fechavencimiento)->lt(Carbon::today())) {
            return 'PLAN VENCIDO';
        }
        if ((int) $this->creditos < 1) {
            return 'SIN CLASES';
        }
        return null;
    }

    /** Filtra por estado del plan en SQL (para paginar). Misma regla que estadoPlan() */
    public function scopeEstadoPlan($query, $estado)
    {
        $planVigente = function ($q) {
            $q->selectRaw('1')->from('plan')
                ->whereColumn('plan.id', 'cliente.plan_id')
                ->whereNull('plan.deleted_at');
        };
        if ($estado == 'SIN PLAN') {
            return $query->whereNotExists($planVigente);
        }
        $congelado = function ($q) {
            $q->selectRaw('1')->from('congelamiento')
                ->whereColumn('congelamiento.cliente_id', 'cliente.id')
                ->where('congelamiento.situacion', '=', 'ACTUAL');
        };
        // Vencimiento del último pago validado del plan actual
        $vencimiento = '(SELECT p.fechavencimiento FROM planpago p WHERE p.cliente_id = cliente.id AND p.plan_id = cliente.plan_id'
            . " AND p.situacion = 'VALIDADO' AND p.deleted_at IS NULL ORDER BY p.id DESC LIMIT 1)";
        $hoy = Carbon::today()->format('Y-m-d');
        $query->whereExists($planVigente)->whereNotExists($congelado);
        if ($estado == 'PLAN VENCIDO') {
            return $query->whereRaw($vencimiento . ' < ?', array($hoy));
        }
        if ($estado == 'SIN CLASES') {
            return $query->where('creditos', '<', 1)->whereRaw('NOT COALESCE(' . $vencimiento . ' < ?, 0)', array($hoy));
        }
        return $query;
    }

    /**
     * Método para listar
     * @param  model $query modelo
     * @param  string $name  nombre
     * @return sql        sql
     */
    public function scopelistar($query, $name, $type)
    {
        return $query->where(function($subquery) use($name)
		            {
		            	if (!is_null($name)) {
		            		$subquery->where('name', 'LIKE', '%'.$name.'%');
		            	}
		            })
        			->where(function($subquery) use($type)
		            {
		            	if (!is_null($type)) {
		            		$subquery->where('type', '=', $type);
		            	}
		            })
        			->orderBy('firstname', 'ASC');
    }

}
