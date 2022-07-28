<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCommonvaluesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('common_values', function (Blueprint $table){
            $table->id()->comment('id');
            $table->string('name')->comment('変数名', 30)->unique();
            $table->string('name_system')->comment('システム変数名', 30)->unique();
            $table->string('value')->comment('値', 255);
            $table->string('value_2nd')->comment('値2', 255)->nullable();
            $table->date('start_2nd_on')->comment('値改訂日')->default('2049/12/31')->nullable();
            $table->string('remarks')->comment('備考', 200)->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
        });
        DB::statement("alter table wnetdb_test.common_values comment '共通変数';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('common_values');
    }
}
