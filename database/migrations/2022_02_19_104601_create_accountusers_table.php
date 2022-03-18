<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAccountusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accountusers', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->foreignId('user_id')->references('id')->on('users')->comment('ログイン名');
            $table->string('name', 12)->default('事業所略称')->comment('アカウント名');
            $table->string('password', 16)->default('password')->comment('パスワード');
            $table->foreignId('company_id')->references('id')->on('companies')->comment('企業');
            $table->foreignId('department_id')->references('id')->on('departments')->comment('部門');
            $table->foreignId('businessunit_id')->references('id')->on('businessunits')->comment('事業所');
            $table->date('start_on')->default(NULL)->nullable()->comment('開始日');
            $table->date('end_on')->default('2049/12/31')->nullable()->comment('終了日');
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
            $table->unique(['user_id','name',]);
        });
        DB::statement("alter table wnetdb_test.accountusers comment 'アカウントユーザー';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accountusers');
    }
}
