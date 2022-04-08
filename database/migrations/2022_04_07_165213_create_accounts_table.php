<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->foreignId('company_id')->references('id')->on('companies')->comment('企業');
            $table->foreignId('user_id')->references('id')->on('users')->comment('ユーザー');
            $table->foreignId('jobtype_id')->references('id')->on('jobtypes')->comment('業務種類');
            $table->integer('self_accesslevel')->default(2)->comment('自己権限');
            $table->integer('unit_accesslevel')->default(0)->comment('事業所内権限');
            $table->integer('department_accesslevel')->default(0)->comment('部署内権限');
            $table->integer('company_accesslevel')->default(0)->comment('企業内権限');
            $table->integer('system_accesslevel')->default(0)->comment('システム内権限');
            $table->date('start_on')->default(NULL)->nullable()->comment('開始日');
            $table->date('end_on')->default('2049/12/31')->nullable()->comment('終了日');
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
            $table->unique(['company_id','user_id','jobtype_id',]);
        });
        DB::statement("alter table wnetdb_test.accounts comment 'アカウント';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
