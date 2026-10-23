<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;
    protected $table = 'cliente';
    protected $dates = ['deleted_at'];

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
