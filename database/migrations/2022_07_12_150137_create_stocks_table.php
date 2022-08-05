<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('businessunit_id')->comment('事業所')->references('id')->on('businessunits');
            $table->foreignId('productitem_id')->comment('規番コード')->references('id')->on('productitems');
            $table->foreignId('stockshell_id')->comment('棚番号')->references('id')->on('stockshells');
            $table->integer('stockshellno')->comment('棚内順')->nullable();
            $table->foreignId('stockshell_id_2nd')->comment('棚番号2');
            $table->integer('stockshellno2')->comment('棚番号2内順')->nullable();
            $table->integer('currentstock')->comment('現在庫数');
            $table->bigInteger('stockstatus_opt')->comment('在庫状態');
            $table->boolean('is_autoreorder')->comment('自動発注');
            $table->integer('reorderpoint')->comment('発注点')->nullable();
            $table->integer('maxstock')->comment('上限在庫数')->nullable();
            $table->date('stockupdeted_on')->comment('現在庫修正日')->default('2000/01/01')->nullable();
            $table->string('remark', 255)->comment('備考')->nullable();
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
            $table->unique(['businessunit_id','productitem_id',]);
        });
        DB::statement("alter table wnetdb_test.stocks comment '在庫';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stocks');
    }
}
