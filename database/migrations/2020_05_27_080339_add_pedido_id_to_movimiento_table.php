<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPedidoIdToMovimientoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimiento', function (Blueprint $table) {
            $table->integer('pedido_id')->unsigned()->nullable();
            $table->foreign('pedido_id')->references('id')->on('pedido')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movimiento', function (Blueprint $table) {
            $table->dropColumn('pedido_id');
        });
    }
}
