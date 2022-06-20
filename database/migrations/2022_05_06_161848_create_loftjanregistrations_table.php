<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateLoftjanregistrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loftjanregistrations', function (Blueprint $table){
            $table->id()->comment('id');
            $table->string('region')->comment('領域', 20);
            $table->string('linecode')->comment('ラインコード', 2);
            $table->string('itemcode')->comment('アイテムコード', 4);
            $table->string('jancode')->comment('単品コード', 13);
            $table->string('shopcode')->comment('店舗コード', 3);
            $table->integer('price')->comment('売単価');
            $table->decimal('purchaseprice')->comment('原単価', 8,2);
            $table->string('pricetermcode')->comment('消化コード', 4);
            $table->string('finishdatestr')->comment('終了宣言日', 8);
            $table->string('updatedatestr')->comment('更新年月日', 8);
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
            $table->unique(['jancode','shopcode',]);
        });
        DB::statement("alter table wnetdb_test.loftjanregistrations comment '対応カラム';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loftjanregistrations');
    }
}
