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
            $table->string('region', 20)->comment('領域');
            $table->string('linecode', 2)->comment('ラインコード');
            $table->string('itemcode', 4)->comment('アイテムコード');
            $table->string('jancode', 13)->comment('単品コード');
            $table->string('shopcode', 3)->comment('店舗コード');
            $table->integer('price')->comment('売単価');
            $table->decimal('purchaseprice', 8,2)->comment('原単価');
            $table->string('pricetermcode', 4)->comment('消化コード');
            $table->string('finishdatestr', 8)->comment('終了宣言日');
            $table->string('updatedatestr', 8)->comment('更新年月日');
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
            $table->unique(['jancode','shopcode',]);
        });
        DB::statement("alter table wnetdb_test.loftjanregistrations comment 'ロフトJAN登録';");
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
