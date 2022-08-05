<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrderdetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table){
            $table->id()->comment('id');
<<<<<<< HEAD
            $table->foreignId('orderlabel_id')->comment('発注No')->references('id')->on('orderlabels');
=======
            $table->foreignId('orderlabel_id')->comment('発注No')->references('id')->on('order_labels');
>>>>>>> 68e982505ccca6c88bf8bfe174179bbd3edad0a7
            $table->integer('detail_no')->comment('発注行No');
            $table->foreignId('productitem_id')->comment('規番コード')->references('id')->on('productitems');
            $table->integer('regularprice')->comment('定価');
            $table->integer('price')->comment('発注単価');
            $table->integer('quantity')->comment('発注数量');
            $table->smallInteger('taxrate')->comment('消費税率')->default(10);
            $table->boolean('is_fixed')->comment('手配済');
<<<<<<< HEAD
            $table->string('remark', 255)->comment('備考')->default('')->nullable();
            $table->integer('available_quantity')->comment('入荷済数量')->default(0);
            $table->boolean('is_completed')->comment('完了フラグ')->default(0);
            $table->bigInteger('transaction_no')->comment('取引管理No')->default(0);
            $table->integer('old13id')->comment('旧13ID')->default(0)->nullable();
            $table->integer('old14id')->comment('旧14ID')->default(0)->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
=======
            $table->string('remark')->comment('備考', 255)->default('')->nullable();
            $table->integer('available_quantity')->comment('入荷済数量')->default(0);
            $table->boolean('is_completed')->comment('完了フラグ')->default(0);
            $table->bigInteger('transaction')->comment('取引管理No')->default(0);
            $table->integer('old13')->comment('旧13ID')->default(0)->nullable();
            $table->integer('old14')->comment('旧14ID')->default(0)->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
>>>>>>> 68e982505ccca6c88bf8bfe174179bbd3edad0a7
            $table->unique(['orderlabel_id','detail_no',]);
        });
        DB::statement("alter table wnetdb_test.order_details comment '発注明細';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details');
    }
}
