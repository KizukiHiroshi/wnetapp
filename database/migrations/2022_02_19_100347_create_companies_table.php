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
        Schema::create('companies', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->string('code', 4)->unique()->comment('code');
            $table->string('name', 30)->comment('企業名');
            $table->string('name_kana', 30)->comment('カナ');
            $table->string('name_short', 10)->comment('略称');
            $table->string('postalcode', 8)->comment('郵便番号');
            $table->string('address1', 40)->comment('住所1');
            $table->string('address2', 40)->nullable()->comment('住所2');
            $table->string('telno', 13)->comment('電話');
            $table->string('foxno', 13)->nullable()->comment('FAX');
            $table->string('url', 255)->nullable()->comment('URL');
            $table->boolean('is_buyer')->default(0)->comment('顧客先FLAG');
            $table->boolean('is_vendor')->default(0)->comment('仕入先FLAG');
            $table->boolean('can_order')->default(0)->comment('購入機能有無');
            $table->boolean('can_sale')->default(0)->comment('販売機能有無');
            $table->boolean('can_stock')->default(0)->comment('在庫機能有無');
            $table->date('start_on')->default(NULL)->nullable()->comment('開始日');
            $table->date('end_on')->default('2049/12/31')->nullable()->comment('終了日');
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
