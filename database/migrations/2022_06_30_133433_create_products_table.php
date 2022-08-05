<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('brand_id')->comment('ブランド')->references('id')->on('brands');
            $table->string('name', 30)->comment('商品');
            $table->string('name_kana', 30)->comment('カナ')->nullable();
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
            $table->unique(['brand_id','name',]);
        });
        DB::statement("alter table wnetdb_test.products comment '商品';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
