<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('company_id')->comment('企業');
            $table->string('code', 5)->unique()->comment('code');
            $table->string('name', 30)->unique()->comment('部門名');
            $table->string('name_short', 10)->comment('略称');
            $table->integer('department_hierarchy')->comment('部門階層');
            $table->string('departmentpath', 50)->comment('部門パス');
            $table->date('start_on')->default(NULL)->nullable()->comment('開始日');
            $table->date('end_on')->default('2049/12/31')->nullable()->comment('終了日');
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
        });
        DB::statement("alter table wnetdb_test.departments comment '部門';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departments');
    }
}
