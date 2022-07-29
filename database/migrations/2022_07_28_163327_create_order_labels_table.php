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
            $table->string('order_no')->comment('発注No', 13)->unique();
            $table->date('order_on')->comment('発注日');
            $table->foreignId('order-company_id')->comment('発注企業')->references('id')->on('companies');
            $table->foreignId('order-businessunit_id')->comment('発注事業所')->references('id')->on('businessunits');
            $table->string('ordered_by')->comment('担当者', 12)->nullable();
            $table->foreignId('getorder-company_id')->comment('発注先企業')->references('id')->on('companies');
            $table->foreignId('getorder-businessunit_id')->comment('発注先事業所')->references('id')->on('businessunits');
            $table->boolean('need_deliverydate')->comment('納期連絡有無')->default(0);
            $table->date('due_date')->comment('指定納期')->nullable();
            $table->integer('regularprice_total')->comment('定価合計');
            $table->integer('price_total')->comment('金額合計');
            $table->integer('tax_total')->comment('消費税金額');
            $table->foreignId('delivery-businessunit_id')->comment('入荷先')->references('id')->on('businessunits');
            $table->date('published_on')->comment('発行日')->nullable();
            $table->string('remark')->comment('備考')->default('')->nullable();
            $table->boolean('is_completed')->comment('完了フラグ')->default(0);
            $table->bigInteger('transaction')->comment('取引管理No')->default(0);
            $table->integer('old13id')->comment('旧13ID')->default(0)->nullable();
            $table->integer('old14id')->comment('旧14ID')->default(0)->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
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