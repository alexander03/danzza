<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMotivoIdToMovimientoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimiento', function (Blueprint $table) {
            $table->integer('motivo_id')->after('movimiento_id')->unsigned()->nullable();
            $table->foreign('motivo_id')->references('id')->on('motivo')->onDelete('restrict')->onUpdate('restrict');
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
            $table->dropColumn('motivo_id');
        });
    }
}
