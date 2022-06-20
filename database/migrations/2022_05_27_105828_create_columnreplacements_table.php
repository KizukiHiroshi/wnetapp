<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateColumnreplacementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('columnreplacements', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('tablereplacement_id')->comment('置換名')->references('id')->on('tablereplacements');
            $table->tinyInteger('no')->comment('no');
            $table->string('oldcolumnname')->comment('旧カラム名', 50);
            $table->boolean('is_keycolumn')->comment('キーカラム区分')->default(0);
            $table->string('newcolumnname')->comment('新カラム名', 50);
            $table->string('remarks')->comment('備考', 200);
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
            $table->unique(['oldcolumnname','newcolumnname',]);
        });
        DB::statement("alter table wnetdb_test.columnreplacements comment 'カラム置換';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('columnreplacements');
    }
}
