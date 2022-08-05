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
            $table->string('code', 13)->comment('規番コード')->unique();
            $table->string('jancode', 13)->comment('JANコード')->nullable();
            $table->string('prdcode', 20)->comment('商品コード')->nullable();
            $table->string('name', 60)->comment('規格');
            $table->string('name_kana', 60)->comment('カナ')->nullable();
            $table->string('color', 40)->comment('色')->nullable();
            $table->string('size', 20)->comment('サイズ')->nullable();
            $table->boolean('is_janprinted')->comment('JAN印刷有')->nullable();
            $table->bigInteger('pricelabel_opt')->comment('値札種類')->nullable();
            $table->integer('unit')->comment('単位数');
            $table->bigInteger('unitname_opt')->comment('単位名')->default(1)->nullable();
            $table->integer('regularprice')->comment('定価');
            $table->integer('regularprice_2nd')->comment('新定価')->default(0)->nullable();
            $table->date('start_2nd_on')->comment('定価改訂日')->default('2049/12/31')->nullable();
            $table->string('url', 100)->comment('URL')->nullable();
            $table->string('image', 100)->comment('画像')->nullable();
            $table->string('remark', 255)->comment('備考')->nullable();
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
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
