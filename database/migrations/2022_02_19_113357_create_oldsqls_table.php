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
            $table->dateTime('transed_at')->default(NULL)->nullable()->comment('転記日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
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
