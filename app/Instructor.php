<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instructor extends Model
{
    use SoftDeletes;
    protected $table = 'instructor';
    protected $dates = ['deleted_at'];
    
}
