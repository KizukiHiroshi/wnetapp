<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateConcernsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('concerns', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('jobtype_id')->comment('業務種類')->references('id')->on('jobtypes');
            $table->string('name', 40)->comment('テーマ')->default('')->unique();
            $table->string('content', 255)->comment('内容')->default('');
            $table->string('importance', 2)->comment('重要度')->default('C');
            $table->integer('priority')->comment('優先順位')->default(100);
            $table->string('solution', 255)->comment('解決案')->default('未');
            $table->boolean('is_solved')->comment('処理済')->default(0);
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
        });
        DB::statement("alter table wnetdb_test.concerns comment 'どうする';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('concerns');
    }
}
