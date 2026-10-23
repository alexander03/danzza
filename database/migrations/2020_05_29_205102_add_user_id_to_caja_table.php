<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdToCajaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->integer('user_id')->after('nombre')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('user')->onDelete('restrict')->onUpdate('restrict');
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
            $table->dropColumn('user_id');
        });
    }
}
