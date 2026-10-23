<?php

namespace App;

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
}
