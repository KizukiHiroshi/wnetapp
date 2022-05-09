<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCodereplacementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('codereplacements', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->foreignId('columnreplacement_id')->comment('読替カラム')->references('id')->on('columnreplacements');
            $table->string('oldcode')->comment('旧コード', 50);
            $table->string('newcode')->comment('新コード', 50);
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
            $table->unique(['columnreplacement_id','oldcode',]);
        });
        DB::statement("alter table wnetdb_test.codereplacements comment '対応コード';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('codereplacements');
    }
}
