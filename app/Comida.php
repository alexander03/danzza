<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comida extends Model
{
    use SoftDeletes;
    protected $table = 'comida';

    public function persona()
    {
        return $this->belongsTo('App\Person', 'persona_id');
    }

}
