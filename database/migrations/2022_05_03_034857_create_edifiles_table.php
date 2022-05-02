<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEdifilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('edifiles', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->foreignId('company_id')->comment('企業')->references('id')->on('companies');
            $table->string('name')->comment('利用名', 50)->unique();
            $table->string('or_up_down')->comment('Up_or_Down', 1)->default('d');
            $table->string('filenamepattern')->comment('ファイル名パターン', 50)->default('');
            $table->string('postingtable')->comment('転記先テーブル', 30)->default('');
            $table->string('loginurl')->comment('LoginUrl', 255)->default('');
            $table->string('loginid')->comment('LoginId', 20)->default('');
            $table->string('loginpassword')->comment('LoginPwd', 20)->default('');
            $table->string('processurl')->comment('直接Url', 255)->default('');
            $table->string('frequency')->comment('処理頻度', 30)->default('');
            $table->date('start_on')->comment('開始日')->default(NULL)->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
        });
        DB::statement("alter table wnetdb_test.edifiles comment 'EDIファイル';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('edifiles');
    }
}
