<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateJobtypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobtypes', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->string('code', 3)->unique()->comment('code');
            $table->string('name', 20)->default('')->comment('業務種類');
            $table->string('name_system', 40)->default('')->unique()->comment('Jobtype');
            $table->string('remarks', 80)->default('')->nullable()->comment('備考');
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
        });
        DB::statement("alter table wnetdb_test.jobtypes comment '業務種類';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobtypes');
    }
}
