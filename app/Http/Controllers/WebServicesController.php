<?php

namespace App\Http\Controllers;

use Hash;
use Validator;
use Illuminate\Http\Request;
use App\Categoria;
use App\Category;
use App\Producto;
use App\Promocion;
use App\Detallemovimiento;
use App\Pedido;
use App\Plan;
use App\Sucursal;
use App\User;
use App\DetallePedido;
use App\Cliente;
use Illuminate\Support\Facades\DB;
use App\Librerias\Libreria;
use DateTime;
use GuzzleHttp\Client;

class WebServicesController extends Controller
{
    public function cargarMegaMenu(){
    	$categorias = Category::join("categoria","categoria.categoria_id","=","category.id")->whereNull("categoria.deleted_at")->orderBy("category.nombre","ASC")->select("category.*")->distinct("category.id")->get();
    	foreach ($categorias as $key => $value) {
    		$value->categorias = Categoria::join("producto","categoria.id","=","producto.categoria_id")->where("categoria.categoria_id","=",$value->id)->whereNull("producto.deleted_at")->select("categoria.*")->distinct("categoria.id")->get();
            foreach ($value->categorias as $key => $value2) {
                $value2->productos;
            }
    	}
    	return json_encode(array("categorias"=>$categorias));
    }

    public function principal(){
    	$detalles = Detallemovimiento::join("movimiento","movimiento.id","=","detallemovimiento.movimiento_id")->join("producto","producto.id","=","detallemovimiento.producto_id")->whereNull("producto.deleted_at")->where("movimiento.tipomovimiento_id","=","2")->whereNotIn('movimiento.situacion',['A'])->groupBy("detallemovimiento.producto_id")->select("detallemovimiento.producto_id",DB::raw('sum(detallemovimiento.cantidad) as sumcantidad'))->orderBy("sumcantidad","DESC")->LIMIT(10)->get();
    	foreach ($detalles as $key => $value) {
    		$value->producto = Producto::find($value->producto_id);
    		//$value->producto->marca;
    		//$value->producto->unidad;
    	}

        $detalles2 = Category::whereNotNull("orderweb")->orderBy("orderweb","ASC")->get();
        foreach ($detalles2 as $key => $value) {
         $value->categoria = Category::find($value->id);
        }
    	//return json_encode($detalles2);
    	return json_encode(array("productos"=>$detalles,"categorias"=>$detalles2));
    }

    public function catalogo(Request $request){
        $categorias = Category::join("categoria","categoria.categoria_id","=","category.id")->join("producto","categoria.id","=","producto.categoria_id")->whereNull("producto.deleted_at")->whereNull("categoria.deleted_at")->groupBy("category.id")->select("category.id", "category.nombre", DB::raw('count(category.id) as cantidad'))->orderBy("category.nombre","ASC")->get();
        foreach ($categorias as $key => $value) {
            $value->categorias =  Categoria::join("producto","categoria.id","=","producto.categoria_id")->where("categoria.categoria_id","=",$value->id)->whereNull("producto.deleted_at")->groupBy("categoria.id")->select("categoria.id", "categoria.nombre", DB::raw('count(categoria.id) as cantidad'))->orderBy("categoria.nombre","ASC")->get();
        }
    	return json_encode(array("categorias"=>$categorias));
    }

    public function buscarProducto(Request $request){
    	$categorias_id = explode(",", $request->input("categorias"));
    	$pagina = $request->input("page");

    	$resultado = Producto::orderBy("producto.nombre","ASC");
    	if($request->input("categorias")!= null){
    		$resultado = $resultado->whereIn("producto.categoria_id",$categorias_id);
    	}
    	$productos = $resultado->get();

    	$filas = "12";

    	$clsLibreria     = new Libreria();
        $paramPaginacion = $clsLibreria->generarPaginacion($productos, $pagina, $filas, "PRODUCTO");
        $paginacion      = $paramPaginacion['cadenapaginacion'];
        $paginaactual    = $paramPaginacion['nuevapagina'];
        $productos       = $resultado->paginate($filas);
        //$request->replace(array('page' => $paginaactual));

    	return json_encode(array("productos"=>$productos,"paginacion"=>$paginacion,"paginaactual"=>$paginaactual));
    }

    public function productoautocompletar(Request $request)
    {
    	$searching = $request->input("query");
        $resultado        = Producto::
        	where(function ($sql) use ($searching) {
                $sql->where("producto.nombre", 'LIKE', '%' . strtoupper($searching) . '%');
            })
            ->whereNull('producto.deleted_at')->orderBy('nombre', 'ASC');
        $list = $resultado->select('producto.*')->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                'label' => trim($value->nombre),
                'id'    => $value->id,
                'name' => trim($value->nombre),
                'precio'   => number_format($value->precioventa,2),
                'descripcion'   => "",
                'imagen'   => $value->archivo,
            );
        }
        return json_encode($data);
    }

    public function producto(Request $request)
    {
    	$producto_id = $request->input("producto_id");
        $producto = Producto::find($producto_id);
        $producto->marca;
        $producto->unidad;
        $producto->categoria;
        $producto->categoria->categoriapadre;
        $producto->precioventa = number_format($producto->precioventa,2);

        $productos = Producto::where("categoria_id","=",$producto->categoria_id)->where("id","<>",$producto_id)->orderBy("producto.nombre")->limit(10)->get();
        return json_encode(array("producto"=>$producto,"productos"=>$productos));
    }

    public function registrarPedido(Request $request){
        
        $dat = array();

        try {
            $error = DB::transaction(function () use ($request,  &$dat) {
                //-------------------CREAR PEDIDO------------------------
                    $Pedido       = new Pedido();
                    $Pedido->cliente_id = 1;
                    $Pedido->nombre = Libreria::getParam($request->input('nombre'));
                    $Pedido->ruc = Libreria::getParam($request->input('ruc'));
                    $Pedido->dni = Libreria::getParam($request->input('dni'));
                    $Pedido->telefono = Libreria::getParam($request->input('telefono'));
                    $Pedido->direccion = Libreria::getParam($request->input('direccion'));
                    $Pedido->referencia = Libreria::getParam($request->input('referencia'));
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
                    $detalles = json_decode($request->input('listProducto'));
                    $total = 0;
                    //dd($request);
                    foreach ($detalles as $detalle) {
                        $Detalle = new DetallePedido();
                        $Detalle->pedido_id = $Pedido->id;
                        $precioventa = 0;
                        if($detalle->tipo =="P"){
                            $productoOb = Producto::find($detalle->producto_id);
                            $precioventa = $productoOb->precioventa;
                            $Detalle->producto_id=$detalle->producto_id;
                        }else{
                            $productoOb = Promocion::find($detalle->producto_id);
                            $precioventa = $productoOb->precioventa;
                            $Detalle->promocion_id=$detalle->producto_id;
                        }
                        $Detalle->cantidad = $detalle->cantidad;
                        // $Detalle->precioventa = $detalle->precio;
                        $Detalle->precioventa = $precioventa;
                        $total = $total + $precioventa * $detalle->cantidad;
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

                $dat[0] = array("respuesta" => "OK");
            });
        } catch (\Exception $e) {
            return json_encode(array("respuesta"=>"ERROR", "msg"=>$e->getMessage()));
        }
        return is_null($error) ? json_encode($dat[0]) : $error;
    }

    public function modalCliente(Request $request){
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Cliente';
        $cliente = null;
        $formData = array('webservices.storeCliente');
        $cboPlan = array('' => '--Seleccione--') + Plan::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboSituacion = array('ACTIVO'=>'ACTIVO','BLOQUEADO'=>'BLOQUEADO');
        $formData = array('route' => $formData, 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar';
        $cboSucursal = array('' => '--Seleccione--') + Sucursal::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        return view('app.cliente.mant')->with(compact('cliente', 'formData', 'entidad', 'boton', 'listar', 'cboPlan','cboSituacion','cboSucursal'));
    }

    public function storeCliente(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
            'nombres' => 'required|max:500',
            'apellidopaterno' => 'required|max:500',
            'apellidomaterno' => 'required|max:500',
            'documento' => 'required',
            //'plan_id' => 'required',
            'usuario' => 'required',
            'password' => 'required',
            'fechanacimiento' => 'required|date_format:Y-m-d',
        );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'apellidopaterno.required'         => 'Debe ingresar un apellido paterno',
            'password.required'         => 'Debe ingresar una contraseña',
            'fechanacimiento.required'         => 'Debe ingresar una fecha de nacimiento'
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function () use ($request) {
            $person = new Cliente();
            $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
            $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
            $person->nombres = strtoupper($request->input('nombres'));
            $person->documento = strtoupper($request->input('documento'));
            $person->direccion = strtoupper($request->input('direccion'));
            $person->creditos = strtoupper($request->input('creditos')) + 0;
            $person->fechanacimiento = strtoupper($request->input('fechanacimiento'));
            $person->dniapoderado = strtoupper($request->input('dniapoderado'));
            $person->correo = strtoupper($request->input('correo'));
            $person->plan_id = strtoupper($request->input('plan_id')) + 0;
            $person->celular = strtoupper($request->input('celular'));
            $person->clases = strtoupper($request->input('clases')) + 0;
            $person->situacion = strtoupper($request->input('situacion'));
            $person->sucursal_id = $request->input('sucursal_id');
            $person->save();

            $usuario = new User();
            $usuario->cliente_id = $person->id;
            $usuario->login        = $request->input('usuario');
            $usuario->password     = Hash::make($request->input('password'));
            $usuario->usertype_id  = 5;
            $usuario->sucursal_id  = 4;
            $usuario->name  = "";
            $usuario->email  = "";
            $usuario->issuperuser  = "0";
            $usuario->isstaff  = "1";
            $usuario->isactive  = "1";
            $usuario->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function modalPlanes(Request $request){
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Plan';
        $planes = Plan::where('situacion', '=', 'ACTIVO')->orderBy('orden','asc')->get();
        $formData = array('webservices.storePlan');
        $cboSituacion = array('ACTIVO'=>'ACTIVO','BLOQUEADO'=>'BLOQUEADO');
        $formData = array('route' => $formData, 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar';
        // "Ver horarios" (solo consulta): sedes y máximo de días del modo "Varios días"
        $sedes        = Sucursal::orderBy('nombre', 'asc')->get();
        $maxDiasRango = \App\Http\Controllers\ClienteController::diasMaximoRango();
        return view('app.plan.list-home')->with(compact('planes', 'formData', 'entidad', 'boton', 'listar','cboSituacion', 'sedes', 'maxDiasRango'));
    }

    /**
     * Horarios públicos (sin login, solo consulta) para "Ver horarios" de la lista de planes.
     * Usa la misma lógica que la reserva del cliente (días de la semana, vigencia, cupos y
     * horarios que ya terminaron hoy), pero no permite reservar.
     *   ?sede_id=1&fecha=2026-10-15  o  ?sede_id=1&fechainicio=...&fechafin=...
     */
    public function horarios(Request $request)
    {
        return app(\App\Http\Controllers\ClienteController::class)->horarios($request);
    }

    public function buscarDNI(Request $request)
    {
        $respuesta = array();
        $dni = $request->input('dni');
        $client = new Client();
        $res = $client->get('http://facturae-garzasoft.com/facturacion/buscaCliente/BuscaCliente2.php?' . 'dni=' . $dni . '&fe=N&token=qusEj_w7aHEpX');
        if ($res->getStatusCode() == 200) { // 200 OK
            $response_data = $res->getBody()->getContents();
            $respuesta = json_decode($response_data);
        }
        return json_encode($respuesta);
    }
}
