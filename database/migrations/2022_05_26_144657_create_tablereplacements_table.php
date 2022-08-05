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
        Schema::create('tablereplacements', function (Blueprint $table){
            $table->id()->comment('id');
            $table->integer('no')->comment('no');
<<<<<<< HEAD
            $table->string('name', 30)->comment('置換名');
            $table->string('systemname', 30)->comment('replacementname');
            $table->string('oldtablename', 30)->comment('旧テーブル名');
            $table->string('newtablename', 30)->comment('新テーブル名');
            $table->dateTime('latest_created')->comment('直近登録');
            $table->dateTime('latest_updated')->comment('直近更新');
            $table->string('maxvalue', 50)->comment('最大値');
            $table->string('remarks', 200)->comment('備考')->nullable();
=======
            $table->string('name')->comment('置換名', 30);
            $table->string('systemname')->comment('replacementname', 30);
            $table->string('oldtablename')->comment('旧テーブル名', 30);
            $table->string('newtablename')->comment('新テーブル名', 30);
            $table->dateTime('latest_created')->comment('直近登録');
            $table->dateTime('latest_updated')->comment('直近更新');
            $table->string('maxvalue')->comment('最大値', 45);
            $table->string('remarks')->comment('備考', 200)->nullable();
>>>>>>> 68e982505ccca6c88bf8bfe174179bbd3edad0a7
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
<<<<<<< HEAD
            $table->string('updated_by', 12)->comment('更新者');
=======
            $table->string('updated_by')->comment('更新者', 12);
>>>>>>> 68e982505ccca6c88bf8bfe174179bbd3edad0a7
            $table->unique(['name','oldtablename','newtablename',]);
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
