<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table){
            $table->id()->comment('id');
            $table->string('code', 4)->comment('code')->unique();
            $table->string('name', 30)->comment('企業名');
            $table->string('name_kana', 30)->comment('カナ');
            $table->string('name_short', 10)->comment('略称');
            $table->string('postalcode', 8)->comment('郵便番号');
            $table->string('address1', 40)->comment('住所1');
            $table->string('address2', 40)->comment('住所2')->nullable();
            $table->string('telno', 14)->comment('電話');
            $table->string('telno2', 14)->comment('電話2')->nullable();
            $table->string('faxno', 14)->comment('FAX')->nullable();
            $table->string('url', 100)->comment('URL')->nullable();
            $table->string('email', 255)->comment('email')->nullable();
            $table->string('remarks', 100)->comment('備考')->nullable();
            $table->boolean('has_businessunit')->comment('事業所有無')->default(0);
            $table->boolean('is_buyer')->comment('顧客先FLAG')->default(0);
            $table->boolean('is_vendor')->comment('仕入先FLAG')->default(0);
            $table->boolean('can_work')->comment('勤怠機能有無')->default(0);
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
        });
        DB::statement("alter table wnetdb_test.companies comment '企業';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
