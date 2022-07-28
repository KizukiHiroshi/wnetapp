<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePerformerincompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('performer_in_companies', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('company_id')->comment('企業名')->references('id')->on('companies');
            $table->date('fiscalyearstart_on')->comment('会計年度開始日')->nullable();
            $table->date('personnelyearstart_on')->comment('人事年度開始日')->nullable();
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
        });
        DB::statement("alter table wnetdb_test.performer_in_companies comment '利用企業';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('performer_in_companies');
    }
}
