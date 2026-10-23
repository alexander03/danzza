<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSucursalEnvioIdToMovimientoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimiento', function (Blueprint $table) {
            $table->integer('sucursal_envio_id')->after('sucursal_id')->unsigned()->nullable();
            $table->foreign('sucursal_envio_id')->references('id')->on('sucursal')->onDelete('restrict')->onUpdate('restrict');
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
            $table->dropColumn('sucursal_envio_id');
        });
    }
}
