<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrderlabelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_labels', function (Blueprint $table){
            $table->id()->comment('id');
            $table->string('order_no', 13)->comment('発注No')->unique();
            $table->date('order_on')->comment('発注日');
            $table->foreignId('order__company_id')->comment('発注企業')->references('id')->on('companies');
            $table->foreignId('order__businessunit_id')->comment('発注事業所')->references('id')->on('businessunits');
            $table->string('ordered_by', 12)->comment('担当者')->nullable();
            $table->foreignId('getorder__company_id')->comment('発注先企業')->references('id')->on('companies');
            $table->foreignId('getorder__businessunit_id')->comment('発注先事業所')->references('id')->on('businessunits');
            $table->boolean('need_deliverydate')->comment('納期連絡有無')->default(0);
            $table->date('due_date')->comment('指定納期')->nullable();
            $table->integer('detail_count')->comment('明細行数');
            $table->integer('regularprice_total')->comment('定価合計');
            $table->integer('price_total')->comment('金額合計');
            $table->integer('tax_total')->comment('消費税金額');
            $table->foreignId('delivery__businessunit_id')->comment('入荷先')->references('id')->on('businessunits');
            $table->boolean('is_recieved')->comment('受信済')->default(0);
            $table->date('published_on')->comment('発行日')->nullable();
            $table->string('remark', 255)->comment('備考')->default('')->nullable();
            $table->boolean('is_completed')->comment('完了フラグ')->default(0);
            $table->bigInteger('alltransaction_no')->comment('取引管理No')->default(0);
            $table->integer('old13id')->comment('旧13ID')->default(0)->nullable();
            $table->integer('old14id')->comment('旧14ID')->default(0)->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
            $table->unique(['order_no','order__company_id',]);
        });
        DB::statement("alter table wnetdb_test.order_labels comment '発注';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_labels');
    }
}
