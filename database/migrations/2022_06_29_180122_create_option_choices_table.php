<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOptionchoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('option_choices', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('jobtype_id')->comment('業務種類')->references('id')->on('jobtypes');
            $table->string('variablename')->comment('変数名', 30);
            $table->string('variablename_systrem')->comment('システム変数名', 30);
            $table->integer('no')->comment('No');
            $table->string('valuename')->comment('値名', 30);
            $table->string('valuename_systrem')->comment('システム値名', 30);
            $table->string('remarks')->comment('備考', 255)->nullable();
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
            $table->unique(['variablename_systrem','valuename_systrem',]);
        });
        DB::statement("alter table wnetdb_test.option_choices comment 'オプション選択肢';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('option_choices');
    }
}
