<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTablereplacementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tablereplacements', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->tinyInteger('no')->comment('no');
            $table->string('name')->comment('置換名', 30);
            $table->string('systemname')->comment('replacementname', 30);
            $table->string('oldtablename')->comment('旧テーブル名', 30);
            $table->string('newtablename')->comment('新テーブル名', 30);
            $table->dateTime('latest_created')->comment('直近登録');
            $table->dateTime('latest_updated')->comment('直近更新');
            $table->string('remarks')->comment('備考', 200)->nullable();
            $table->date('start_on')->comment('開始日')->default(NULL)->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
            $table->unique(['oldtablename','newtablename',]);
        });
        DB::statement("alter table wnetdb_test.tablereplacements comment 'テーブル置換';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tablereplacements');
    }
}
