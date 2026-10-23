<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Horario extends Model
{
    use SoftDeletes;
    protected $table = 'horario';
    protected $dates = ['deleted_at'];

    public function clase()
    {
        return $this->belongsTo('App\Clase', 'clase_id');
    }

    public function sucursal()
    {
        return $this->belongsTo('App\Sucursal', 'sucursal_id');
    }

    public function instructor()
    {
        return $this->belongsTo('App\Instructor', 'instructor_id');
    }

}
