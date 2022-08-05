<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('user_id')->comment('登録者')->references('id')->on('users');
            $table->string('name', 50)->comment('デバイス名')->unique();
            $table->string('key', 200)->comment('デバイスキー');
            $table->integer('paginatecnt')->comment('画面行数')->default(15);
            $table->dateTime('accesstime')->comment('アクセス日時');
            $table->string('accessip', 20)->comment('アクセスIP');
            $table->dateTime('validityperiod')->comment('有効期限日時');
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
        });
        DB::statement("alter table wnetdb_test.devices comment 'デバイス';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
