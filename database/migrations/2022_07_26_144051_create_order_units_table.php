<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrderunitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_units', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('businessunit_id')->comment('発注事業所')->references('id')->on('businessunits');
            $table->string('getorderdayofweek')->comment('受注曜日', 7)->default('0')->nullable();
            $table->string('shippingdayofweek')->comment('出荷曜日', 7)->default('0')->nullable();
            $table->string('remarks')->comment('備考', 200)->nullable();
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
        });
        DB::statement("alter table wnetdb_test.order_units comment '発注事業所';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_units');
    }
}
