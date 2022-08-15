<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateGetorderLabelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('getorder_labels', function (Blueprint $table){
            $table->id()->comment('id');
            $table->string('getorder_no', 13)->comment('受注No')->unique();
            $table->date('getorder_on')->comment('受注日');
            $table->foreignId('getorder__company_id')->comment('受注企業')->references('id')->on('companies');
            $table->foreignId('getorder__businessunit_id')->comment('受注事業所')->references('id')->on('businessunits');
            $table->string('getordered_by', 12)->comment('担当者')->nullable();
            $table->foreignId('order__company_id')->comment('客先企業')->references('id')->on('companies');
            $table->foreignId('order__businessunit_id')->comment('客先事業所')->references('id')->on('businessunits');
            $table->string('guestorder_no', 20)->comment('客先注文番号')->nullable();
            $table->boolean('need_deliverydate')->comment('納期連絡有無')->default(0);
            $table->date('due_date')->comment('指定納期')->nullable();
            $table->integer('regularprice_total')->comment('定価合計');
            $table->integer('price_total')->comment('金額合計');
            $table->integer('tax_total')->comment('消費税金額');
            $table->foreignId('delivery__businessunit_id')->comment('出荷先')->references('id')->on('businessunits');
            $table->boolean('is_fixed')->comment('手配済')->default(0);
            $table->date('published_on')->comment('発行日')->nullable();
            $table->string('estimate_no', 13)->comment('見積No')->nullable();
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
        });
        DB::statement("alter table wnetdb_test.getorder_labels comment '受注';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('getorder_labels');
    }
}
