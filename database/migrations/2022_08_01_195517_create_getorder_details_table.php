<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateGetorderdetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('getorder_details', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('getorderlabel_id')->comment('受注No')->references('id')->on('getorder_labels');
            $table->integer('detail_no')->comment('受注行No');
            $table->foreignId('productitem_id')->comment('規番コード')->references('id')->on('productitems');
            $table->integer('regularprice')->comment('定価');
            $table->integer('price')->comment('受注単価');
            $table->integer('quantity')->comment('受注数量');
            $table->smallInteger('taxrate')->comment('消費税率')->default(10);
            $table->boolean('is_fixed')->comment('手配済');
            $table->string('remark')->comment('備考', 255)->default('')->nullable();
            $table->integer('allocation_quantity')->comment('引当数量')->default(0);
            $table->integer('available_quantity')->comment('出荷済数量')->default(0);
            $table->boolean('is_completed')->comment('完了フラグ')->default(0);
            $table->bigInteger('transaction_no')->comment('取引管理No')->default(0);
            $table->integer('old13id')->comment('旧13ID')->default(0)->nullable();
            $table->integer('old14id')->comment('旧14ID')->default(0)->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
            $table->unique(['getorderlabel_id','detail_no',]);
        });
        DB::statement("alter table wnetdb_test.getorder_details comment '受注明細';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('getorder_details');
    }
}
