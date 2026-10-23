<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCajaIdToMovimientoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimiento', function (Blueprint $table) {
            $table->integer('caja_id')->after('movimiento_id')->unsigned()->nullable();
            $table->foreign('caja_id')->references('id')->on('caja')->onDelete('restrict')->onUpdate('restrict');
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
            $table->dropColumn('caja_id');
        });
    }
}
