<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSequencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sequences', function (Blueprint $table){
            $table->string('name')->comment('連番名', 64);
            $table->string('name_system')->comment('システム連番名', 64)->primary();
            $table->string('nowstring')->comment('連番継続年月日', 10);
            $table->bigInteger('sequence')->comment('連番');
        });
        DB::statement("alter table wnetdb_test.sequences comment '連番管理';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sequences');
    }
}
