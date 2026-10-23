<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUltimaaperturaIdToCajaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->integer('ultimaapertura_id')->after('sucursal_id')->unsigned()->nullable()->default(0);
            //$table->foreign('ultimaapertura_id')->references('id')->on('movimiento')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->dropColumn('ultimaapertura_id');
        });
    }
}
