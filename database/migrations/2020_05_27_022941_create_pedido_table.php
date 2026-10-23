<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cliente_id')->unsigned()->nullable();
            $table->string('dni')->nullable();
            $table->string('ruc')->nullable();
            $table->string('nombre')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->string('direccion')->nullable();
            $table->string('referencia')->nullable();
            $table->decimal('total');
            $table->string('detalle')->nullable();
            $table->decimal('cantidadpago')->nullable();
            $table->integer('tipodocumento_id')->unsigned();
            $table->string('modopago')->nullable();
            $table->string('tarjeta')->nullable();
            $table->string('estado')->default('N'); //N=Nuevo , A=Aceptado, E=Enviado, F=Finalizado , R=Rechazado
            $table->integer('responsable_id')->unsigned()->nullable();
            $table->integer('sucursal_id')->unsigned()->nullable();
            $table->foreign('tipodocumento_id')->references('id')->on('tipodocumento')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('cliente_id')->references('id')->on('user')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('responsable_id')->references('id')->on('user')->onDelete('restrict')->onUpdate('restrict');
            $table->timestamp('fechaaceptado',0)->nullable();
            $table->timestamp('fechaenviado',0)->nullable();
            $table->timestamp('fechafinalizado',0)->nullable();
            $table->timestamp('fecharechazado',0)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedido');
    }
}
