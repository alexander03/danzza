<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modalidad extends Model
{
	 use SoftDeletes;
    protected $table = 'modalidad';
    protected $dates = ['deleted_at'];

    /**
     * Método para listar
     * @param  model $query modelo
     * @param  string $name  nombre
     * @return sql        sql
     */
    public function scopelistar($query, $name)
    {
        return $query->where(function($subquery) use($name)
		            {
		            	if (!is_null($name)) {
		            		$subquery->where('descripcion', 'LIKE', '%'.$name.'%');
		            	}
		            })
        			->orderBy('descripcion', 'ASC');
    }
    
}
