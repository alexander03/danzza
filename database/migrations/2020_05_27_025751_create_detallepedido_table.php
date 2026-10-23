<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetallepedidoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detallepedido', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pedido_id')->unsigned();
            $table->integer('producto_id')->unsigned();
            $table->integer('promocion_id');
            $table->decimal('cantidad');
            $table->decimal('precioventa');
            $table->decimal('subtotal');
            $table->foreign('pedido_id')->references('id')->on('pedido')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('producto_id')->references('id')->on('producto')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::dropIfExists('detallepedido');
    }
}
