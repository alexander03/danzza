<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSucursalIdToStockproductoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stockproducto', function (Blueprint $table) {
            $table->integer('sucursal_id')->after('producto_id')->unsigned()->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onDelete('restrict')->onUpdate('restrict');
      
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stockproducto', function (Blueprint $table) {
             $table->dropColumn('sucursal_id');
        });
    }
}
