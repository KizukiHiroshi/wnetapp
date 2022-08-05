<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBusinessunitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('businessunits', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('company_id')->comment('企業')->references('id')->on('companies');
            $table->string('code', 5)->comment('code');
            $table->string('name', 30)->comment('事業所名');
            $table->string('name_short', 10)->comment('略称');
            $table->string('postalcode', 8)->comment('郵便番号');
            $table->string('address1', 40)->comment('住所1');
            $table->string('address2', 40)->comment('住所2')->nullable();
            $table->string('telno', 13)->comment('電話');
            $table->string('faxno', 13)->comment('FAX')->nullable();
            $table->string('url', 100)->comment('URL')->nullable();
            $table->string('email', 50)->comment('email')->nullable();
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
            $table->unique(['company_id','code',]);
        });
        DB::statement("alter table wnetdb_test.businessunits comment '事業所';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('businessunits');
    }
}
