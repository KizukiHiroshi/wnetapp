<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOldsqlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oldsqls', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->string('sqltype', 6)->default('')->comment('構文種類');
            $table->string('sqltext', 4000)->default('')->comment('SQL文');
            $table->boolean('is_checked')->default(0)->comment('確認済');
            $table->timestamp('transed_at')->nullable()->comment('転記日時');
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
        });
        DB::statement("alter table wnetdb_test.oldsqls comment '旧SQL';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oldsqls');
    }
}
