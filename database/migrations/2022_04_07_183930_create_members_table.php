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
        Schema::create('members', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->foreignId('user_id')->references('id')->on('users')->comment('ログイン名');
            $table->foreignId('businessunit_id')->references('id')->on('businessunits')->comment('事業所');
            $table->foreignId('employtype_id')->references('id')->on('employtypes')->comment('雇用形態');
            $table->string('code', 10)->unique()->comment('コード');
            $table->string('name_sei', 10)->comment('姓');
            $table->string('name_mei', 10)->comment('名');
            $table->string('name_kana', 20)->comment('カナ');
            $table->string('name_short', 12)->comment('略称');
            $table->string('password', 16)->default('password')->comment('パスワード');
            $table->date('start_fulltime_on')->nullable()->comment('社員採用日');
            $table->date('start_2nd_on')->nullable()->comment('異動日');
            $table->foreignId('businessunit_2nd')->comment('異動先');
            $table->foreignId('employtype_2nd')->comment('新雇用形態');
            $table->date('start_on')->default(NULL)->nullable()->comment('開始日');
            $table->date('end_on')->default('2049/12/31')->nullable()->comment('終了日');
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
