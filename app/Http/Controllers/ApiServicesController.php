<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Categoria;
use App\Category;
use App\Producto;
use App\Detallemovimiento;
use App\Pedido;
use App\Person;
use App\Rolpersona;
use App\DetallePedido;
use App\Promocion;
use App\User;
use Hash;
use Illuminate\Support\Facades\DB;
use App\Librerias\Libreria;
use Illuminate\Support\Facades\Auth;

class ApiServicesController extends Controller
{


    public function AppGetCategorias(){
        $categorias = Category::join("categoria","categoria.categoria_id","=","category.id")->whereNull("categoria.deleted_at")->orderBy("category.nombre","ASC")->select("category.*")->distinct("category.id")->get();
        foreach ($categorias as $key => $value) {
            $value->categorias = Categoria::join("producto","categoria.id","=","producto.categoria_id")->where("categoria.categoria_id","=",$value->id)->whereNull("producto.deleted_at")->select("categoria.*")->distinct("categoria.id")->get();
        }
        return json_encode(array("categorias"=>$categorias));
    }

    public function AppGetProductoxCategoria(Request $request){
        $pagina = $request->input("page");

        $resultado = Producto::orderBy("producto.nombre","ASC")->where("producto.categoria_id",$request->input("categoria"));
        
        $productos = $resultado->get();

        // foreach ($productos as $key => $value) {
        //  $value->tipoproducto = "P";
        //  $value->detalles = "";
        // }

        $filas = "12";

        $clsLibreria     = new Libreria();
        $paramPaginacion = $clsLibreria->generarPaginacion($productos, $pagina, $filas, "PRODUCTO");
        $paginacion      = $paramPaginacion['cadenapaginacion'];
        $paginaactual    = $paramPaginacion['nuevapagina'];
        $productos       = $resultado->paginate($filas);
        //$request->replace(array('page' => $paginaactual));
        $response = array();
        foreach ($productos as $value) {
            $value->tipoproducto = "P";
            $value->detalles = "";
            $response[] = $value;
        }

        return json_encode(array("productos"=>$response));
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['login', 'password']);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error'=>'1','mensaje' => 'Invalid Credentials']);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            $user = auth('api')->userOrFail();
            $user->person;
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error'=>'2','mensaje' => "Invalid Token"]);
        }
        return response()->json(['error'=>'0','user' => $user]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            $user = auth('api')->userOrFail();
            auth('api')->logout();
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error'=>'2','mensaje' => "Invalid Token"]);
        }
        return response()->json(['error'=>'0','message' => 'Realizado Correctamente']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            $user = auth('api')->userOrFail();
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error'=>'2','mensaje' => "Invalid Token"]);
        }
        return $this->respondWithToken(auth('api')->refresh());
        
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth('api')->userOrFail();
        return response()->json([
            'error'=>'0',
            'access_token' => $token,
            'user' => $user
            // 'token_type' => 'bearer',
            // 'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function registrarPedido(Request $request){

        try {
            $user = auth('api')->userOrFail();
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error'=>'2','mensaje' => "Invalid Token"]);
        }
        
        $dat = array();

        try {
            $error = DB::transaction(function () use ($request,$user,  &$dat) {
                //-------------------CREAR PEDIDO------------------------
                    $Pedido       = new Pedido();
                    $Pedido->cliente_id = $user->person->id;
                    $Pedido->nombre = Libreria::getParam($request->input('nombre'));
                    $Pedido->ruc = Libreria::getParam($request->input('ruc'));
                    $Pedido->dni = Libreria::getParam($request->input('dni'));
                    $Pedido->telefono = Libreria::getParam($request->input('telefono'));
                    $Pedido->direccion = Libreria::getParam($request->input('direccion'));
                    $Pedido->referencia = Libreria::getParam($request->input('referencia'));
                    $Pedido->correo = Libreria::getParam($request->input('correo'));
                    $Pedido->detalle = Libreria::getParam($request->input('detalle'));
                    $Pedido->tipodocumento_id = $request->input('tipodocumento');
                    $Pedido->delivery = $request->input('delivery');
                    $Pedido->modopago = $request->input('modopago');
                    $Pedido->cantidadpago = $request->input('cantidadpago');
                    if($request->input('modopago')=='TARJETA'){
                        $Pedido->tarjeta =$request->input('tarjeta');
                    }
                    $Pedido->estado = "N";
                    
                    $Pedido->responsable_id = null;
                    $Pedido->sucursal_id = $request->input('sucursal_id');
                    //$Pedido->fechaaceptado =new DateTime();
                    $Pedido->subtotal = 0;
                    $Pedido->igv = 0;
                    $Pedido->total = 0; 
                                    
                    $Pedido->save();
                //---------------------FIN CREAR PEDIDO------------------------

                //---------------------DETALLES VENTA------------------------------
                    $detalles = $request->input('listProducto');
                    $total = 0;
                    //throw new \Exception(json_encode($detalles));
                    
                    foreach ($detalles as $detalle) {
                        $Detalle = new DetallePedido();
                        $Detalle->pedido_id = $Pedido->id;
                        $precioventa = 0;
                        if($detalle["tipo"] =="P"){
                            $productoOb = Producto::find($detalle["producto_id"]);
                            $precioventa = $productoOb->precioventa;
                            $Detalle->producto_id=$detalle["producto_id"];
                        }else{
                            $productoOb = Promocion::find($detalle->producto_id);
                            $precioventa = $productoOb->precioventa;
                            $Detalle->promocion_id=$detalle["producto_id"];
                        }
                        $Detalle->cantidad = $detalle["cantidad"];
                        // $Detalle->precioventa = $detalle->precio;
                        $Detalle->precioventa = $precioventa;
                        $total = $total + $precioventa * $detalle["cantidad"];
                        $Detalle->preciocompra = 0;
                        $Detalle->save();
                    }
                //-----------------------FIN DETALLES VENTA------------------------------

                    if ($request->input('tipodocumento') == "4" || $request->input('tipodocumento') == "3") { //FACTURA O BOLETA
                        $Pedido->subtotal = round($total / 1.18, 2); //82%
                        $Pedido->igv = round($total - $Pedido->subtotal, 2); //18%
                    } else { //TICKET
                        $Pedido->subtotal = $total;
                        $Pedido->igv = 0;
                    }
                    $Pedido->total = $total; 
                    $Pedido->save();

                $dat[0] = array('error'=>'0',"respuesta" => "OK");
            });
        } catch (\Exception $e) {
            return response()->json(['error'=>'1','mensaje' => $e->getMessage()]);
        }
        return is_null($error) ? json_encode($dat[0]) : $error;
    }

    public function editarDatos(Request $request){

        try {
            $user = auth('api')->userOrFail();
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error'=>'2','mensaje' => "Invalid Token"]);
        }
        
        $dat = array();

        try {
            $error = DB::transaction(function () use ($request,$user,  &$dat) {
                $person = $user->person;
                $person->apellidopaterno = strtoupper($request->input('apellidos'));
                // $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
                $person->nombres = strtoupper($request->input('nombres'));
                $person->dni = strtoupper($request->input('dni'));
                $person->ruc = strtoupper($request->input('ruc'));
                $person->direccion = strtoupper($request->input('direccion'));
                $person->email = strtoupper($request->input('email'));
                $person->telefono = strtoupper($request->input('telefono'));
                $person->save();
                // $roles = [""];
                // for ($c = 0; $c < count($roles); $c++) {
                //     $rolpersona = new Rolpersona();
                //     $rolpersona->person_id = $person->id;
                //     $rolpersona->rol_id = $roles[$c];
                //     $rolpersona->save();
                // }
                    

                $dat[0] = array('error'=>'0',"respuesta" => "OK");
            });
        } catch (\Exception $e) {
            return response()->json(['error'=>'1','mensaje' => $e->getMessage()]);
        }
        return is_null($error) ? json_encode($dat[0]) : $error;
    }

    public function misPedidos()
    {
        try {
            $user = auth('api')->userOrFail();
            $user->person;
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error'=>'2','mensaje' => "Invalid Token"]);
        }

        $pedidos = Pedido::where("cliente_id","=",$user->person->id)->orderBy("pedido.created_at","DESC")->get();
        foreach ($pedidos as $key => $pedido) {
            $pedido->sucursal;
            $pedido->fechacreado = date_format($pedido->created_at,'d/m/Y');
            $pedido->detalles = DetallePedido::where("pedido_id",$pedido->id)->get();
            foreach ($pedido->detalles as $key => $value) {
                $value->producto;
            }
        }
        return response()->json(['error'=>'0','pedidos' => $pedidos]);
    }

    public function RegistrarUsuario(Request $request)
    {
        $dat = array();

        try {
            $error = DB::transaction(function () use ($request, &$dat) {
                $person = new Person();
                $person->apellidopaterno = strtoupper($request->input('apellidos'));
                // $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
                $person->nombres = strtoupper($request->input('nombres'));
                $person->dni = null;
                $person->ruc = null;
                $person->direccion = strtoupper($request->input('direccion'));
                $person->email = strtoupper($request->input('email'));
                $person->telefono = strtoupper($request->input('telefono'));
                $person->save();
                $roles = [3,4];
                for ($c = 0; $c < count($roles); $c++) {
                    $rolpersona = new Rolpersona();
                    $rolpersona->person_id = $person->id;
                    $rolpersona->rol_id = $roles[$c];
                    $rolpersona->save();
                }
                $exists = User::where("login","=",$request->input('email'))->limit(1)->get();
                if(count($exists)>0){
                    throw new \Exception("Ya existe un usuario con el mismo correo.");
                }
                $usuario               = new User();
                $usuario->login        = $request->input('email');
                $usuario->password     = Hash::make($request->input('password'));
                $usuario->usertype_id  = 5;
                $usuario->person_id    = $person->id;
                $usuario->sucursal_id  = null;
                $usuario->caja_id      = null;
                $usuario->name  = "";
                $usuario->email  = "";
                $usuario->issuperuser  = "0";
                $usuario->isstaff  = "1";
                $usuario->isactive  = "1";
                $usuario->save();

                $dat[0] = array('error'=>'0',"respuesta" => "OK");
            });
        } catch (\Exception $e) {
            return response()->json(['error'=>'1','mensaje' => $e->getMessage()]);
        }
        return is_null($error) ? json_encode($dat[0]) : $error;
    }
}
