<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Sucursal extends Model
{
    use SoftDeletes;
    protected $table = 'sucursal';

    public function empresa()
    {
        return $this->belongsTo('App\Empresa', 'empresa_id');
    }

    public function cajas()
    {
        return $this->hasMany('App\Caja','sucursal_id');
    }

    public function sucursal()
    {
        return $this->belongsTo('App\Sucursal', 'sucursal_id');
    }
    /**
	 * Método para listar las sucursales
	 */
	public function scopelistar($query, $nombre, $empresa_id)
    {
        return $query->where(function($subquery) use($nombre)
		            {
		            	if (!is_null($nombre)) {
		            		$subquery->where('nombre', 'LIKE', '%'.$nombre.'%');
		            	}
		            })
        			->where(function($subquery) use($empresa_id)
		            {
		            	if (!is_null($empresa_id)) {
		            		$subquery->where('empresa_id', '=', $empresa_id);
		            	}
		            })
        			->orderBy('nombre', 'ASC');
    }
}
