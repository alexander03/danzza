<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUltimocierreIdToCajaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caja', function (Blueprint $table) {
             $table->integer('ultimocierre_id')->after('ultimaapertura_id')->unsigned()->nullable()->default(0);
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
            $table->dropColumn('ultimocierre_id');
        });
    }
}
