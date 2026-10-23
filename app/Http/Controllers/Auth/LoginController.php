<?php

namespace App\Http\Controllers\Auth;

use App\Caja;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    protected $login='login';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'login';
    }
    
    /**
     * Redirección después de login según tipo de usuario
     */
    protected function redirectTo()
    {
        $user = Auth::user();

        if ($user->usertype_id === 5) {
            return '/master-cliente';
        }

        return '/home';
    }

    /**
     * Se ejecuta una vez justo después de iniciar sesión.
     * Si entra un administrador (no cliente), se finalizan las reservas cuya clase ya terminó.
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->usertype_id == 1) {
            $this->finalizarReservasPasadas();
        }
        // null: continúa con la redirección normal (redirectTo)
    }

    /**
     * Cambia a FINALIZADO las reservas RESERVADO (no eliminadas) cuya fecha + hora fin
     * del horario ya pasó. No modifica la asistencia ni otros estados.
     */
    private function finalizarReservasPasadas()
    {
        $ahora = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        DB::table('reserva')
            ->join('horario', 'reserva.horario_id', '=', 'horario.id')
            ->where('reserva.situacion', '=', 'RESERVADO')
            ->whereNull('reserva.deleted_at')
            ->whereRaw('TIMESTAMP(DATE(reserva.fecha), horario.horafin) < ?', array($ahora))
            ->update(array(
                'reserva.situacion'  => 'FINALIZADO',
                'reserva.updated_at' => $ahora,
            ));
    }

}
