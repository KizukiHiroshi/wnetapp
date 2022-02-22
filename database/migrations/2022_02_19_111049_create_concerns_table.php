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
        Schema::create('concerns', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->foreignId('jobtype_id')->references('id')->on('jobtypes')->comment('業務種類');
            $table->string('name', 40)->default('')->unique()->comment('テーマ');
            $table->string('content', 255)->default('')->comment('内容');
            $table->string('importance', 2)->default('C')->comment('重要度');
            $table->integer('priority')->default(100)->comment('優先順位');
            $table->string('solution', 255)->default('未')->comment('解決案');
            $table->boolean('is_solved')->default(0)->comment('処理済');
            $table->date('start_on')->default(NULL)->nullable()->comment('開始日');
            $table->date('end_on')->default('2049/12/31')->nullable()->comment('終了日');
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
