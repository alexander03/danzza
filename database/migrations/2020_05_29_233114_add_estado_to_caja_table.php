<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEstadoToCajaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->string('estado')->after('nombre')->default('CERRADA'); //ABIERTA o CERRADA
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
            $table->dropColumn('estado');
        });
    }
}
