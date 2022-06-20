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
            $table->string('code')->comment('code', 4)->unique();
            $table->string('name')->comment('企業名', 30);
            $table->string('name_kana')->comment('カナ', 30);
            $table->string('name_short')->comment('企業略称', 10);
            $table->string('postalcode')->comment('郵便番号', 8);
            $table->string('address1')->comment('住所1', 40);
            $table->string('address2')->comment('住所2', 40)->nullable();
            $table->string('telno')->comment('電話', 14);
            $table->string('telno2')->comment('電話2', 14)->nullable();
            $table->string('foxno')->comment('FAX', 14)->nullable();
            $table->string('url')->comment('URL', 100)->nullable();
            $table->string('email')->comment('email', 50)->nullable();
            $table->string('remarks')->comment('備考', 100)->nullable();
            $table->boolean('has_businessunit')->comment('事業所有無')->default(0);
            $table->boolean('is_buyer')->comment('顧客先FLAG')->default(0);
            $table->boolean('is_vendor')->comment('仕入先FLAG')->default(0);
            $table->boolean('can_work')->comment('勤怠機能有無')->default(0);
            $table->date('start_on')->comment('開始日')->default(NULL)->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
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
