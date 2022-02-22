<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAccountauthoritiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accountauthorities', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->foreignId('accountuser_id')->comment('アカウントユーザー');
            $table->foreignId('jobtype_id')->comment('業務種類');
            $table->integer('level')->comment('権限レベル');
            $table->date('start_on')->default(NULL)->nullable()->comment('開始日');
            $table->date('end_on')->default('2049/12/31')->nullable()->comment('終了日');
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
            $table->unique(['accountuser_id','jobtype_id',]);
        });
        DB::statement("alter table wnetdb_test.accountauthorities comment 'アカウント権限';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accountauthorities');
    }
}
