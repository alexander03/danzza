<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dieta extends Model
{
	 use SoftDeletes;
    protected $table = 'dieta';
    protected $dates = ['deleted_at'];

    public function persona()
    {
        return $this->belongsTo('App\Person', 'persona_id');
    }

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
