<?php

namespace App;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    
    public function usertype()
    {
        return $this->belongsTo('App\Usertype', 'usertype_id');
    }
    
    public function person(){
        return $this->belongsTo('App\Person', 'person_id');
    }

    public function cliente(){
        return $this->belongsTo('App\Cliente', 'cliente_id');
    }
    
    public function sucursal()
    {
        return $this->belongsTo('App\Sucursal', 'sucursal_id');
    }
    
    public function caja()
    {
        return $this->belongsTo('App\Caja', 'caja_id');
    }

    /**
     * Funcón que retorna true si es Admin
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if($this->usertype_id == 4){
            return true;
        }
        return false;
    }
    
    /**
     * Funcón que retorna true si es SuperAdmin
     *
     * @return boolean
     */
    public function isSuperAdmin()
    {
        if ($this->usertype_id == 1) {
            return true;
        }
        return false;
    }

    /**
     * Función para listar Usuarios
     *
     * @param  model $query
     * @param  string $login
     * @param  string $nombre
     * @param  int $sucursal_id
     * @param  int $caja_id
     * @return sql 
     */
    public function scopelistar($query, $login,$nombre = null,$sucursal_id = null, $caja_id = null )
    {
        return $query->leftjoin('person','person.id','=','user.person_id')
                    ->leftjoin('cliente','cliente.id','=','user.cliente_id')
                    ->leftjoin('instructor','instructor.id','=','user.instructor_id')
                    ->where(function($subquery) use($login)
                    {
                        if (!is_null($login)) {
                            $subquery->where('login', 'LIKE', '%'.$login.'%');
                        }
                    })
                    ->where(function($subquery) use($nombre)
                    {
                        if (!is_null($nombre)) {
                            $subquery->where(function($sql) use($nombre){
                                $sql->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'), 'LIKE', '%'.$nombre.'%')
                                    ->orWhere(DB::raw('concat(cliente.apellidopaterno,\' \',cliente.apellidomaterno,\' \',cliente.nombres)'), 'LIKE', '%'.$nombre.'%')
                                    ->orWhere(DB::raw('concat(instructor.apellidopaterno,\' \',instructor.apellidomaterno,\' \',instructor.nombres)'), 'LIKE', '%'.$nombre.'%');

                            });
                        }
                    })
                    ->where(function($subquery) use($sucursal_id)
                    {
                        if (!is_null($sucursal_id)) {
                            $subquery->where('sucursal_id', '=', $sucursal_id);
                        }
                    })
                    ->where(function($subquery) use($caja_id)
                    {
                        if (!is_null($caja_id)) {
                            $subquery->where('caja_id', '=', $caja_id);
                        }
                    })
                    // Nombre según a quién pertenece el usuario: personal, instructor o cliente
                    ->select('user.*',DB::raw('(case when person_id>0 then concat(person.nombres,\' \',person.apellidopaterno,\' \',person.apellidomaterno) when user.instructor_id>0 then concat(instructor.nombres,\' \',instructor.apellidopaterno,\' \',instructor.apellidomaterno) else concat(cliente.nombres,\' \',cliente.apellidopaterno,\' \',cliente.apellidomaterno) end) as persona'))->orderBy('person.apellidopaterno','asc');
    }

    
}
