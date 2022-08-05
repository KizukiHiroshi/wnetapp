<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('user_id')->comment('ログイン名')->references('id')->on('users');
            $table->foreignId('businessunit_id')->comment('所属事業所')->references('id')->on('businessunits');
            $table->foreignId('employtype_id')->comment('雇用形態')->references('id')->on('employtypes');
            $table->string('code', 10)->comment('コード')->unique();
            $table->string('name_sei', 10)->comment('姓');
            $table->string('name_mei', 10)->comment('名');
            $table->string('name_kana', 20)->comment('カナ');
            $table->string('name_short', 12)->comment('略称');
            $table->string('password', 16)->comment('パスワード')->default('password');
            $table->string('email', 50)->comment('個人メール')->nullable();
            $table->smallInteger('hourlywage')->comment('時給')->nullable();
            $table->date('start_fulltime_on')->comment('社員採用日')->nullable();
            $table->date('start_2nd_on')->comment('異動日')->nullable();
            $table->foreignId('businessunit_id_2nd')->comment('新事業所');
            $table->foreignId('employtype_id_2nd')->comment('新雇用形態');
            $table->smallInteger('hourlywage_2nd')->comment('新時給')->nullable();
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
        });
        DB::statement("alter table wnetdb_test.members comment '従業員';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
}
