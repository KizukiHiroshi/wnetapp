<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductitemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productitems', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('product_id')->comment('商品名')->references('id')->on('products');
            $table->string('code')->comment('規番コード', 13)->unique();
            $table->string('jancode')->comment('JANコード', 13)->nullable();
            $table->string('prdcode')->comment('商品コード', 20)->nullable();
            $table->string('name')->comment('規格', 60);
            $table->string('name_kana')->comment('カナ', 60)->nullable();
            $table->string('color')->comment('色', 40)->nullable();
            $table->string('size')->comment('サイズ', 20)->nullable();
            $table->boolean('is_janprinted')->comment('JAN印刷有')->nullable();
            $table->bigInteger('pricelabel_opt')->comment('値札種類')->nullable();
            $table->integer('unit')->comment('単位数');
            $table->bigInteger('unitname_opt')->comment('単位名')->default(1)->nullable();
            $table->integer('regularprice')->comment('定価');
            $table->integer('regularprice_2nd')->comment('新定価')->default(0)->nullable();
            $table->date('start_2nd_on')->comment('定価改訂日')->default('2049/12/31')->nullable();
            $table->string('url')->comment('URL', 100)->nullable();
            $table->string('image')->comment('画像', 100)->nullable();
            $table->string('remark')->comment('備考', 255)->nullable();
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
        });
        DB::statement("alter table wnetdb_test.productitems comment '商品アイテム';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productitems');
    }
}
