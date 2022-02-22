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
        Schema::create('businessunits', function (Blueprint $table) {
            $table->id()->comment('id');
            $table->foreignId('company_id')->comment('企業');
            $table->foreignId('department_id')->comment('部門');
            $table->string('code', 5)->unique()->comment('code');
            $table->string('name', 30)->unique()->comment('事業所名');
            $table->string('name_short', 10)->comment('略称');
            $table->string('postalcode', 8)->comment('郵便番号');
            $table->string('address1', 40)->comment('住所1');
            $table->string('address2', 40)->nullable()->comment('住所2');
            $table->string('telno', 11)->comment('電話');
            $table->string('foxno', 11)->nullable()->comment('FAX');
            $table->string('url', 255)->nullable()->comment('URL');
            $table->string('email', 50)->nullable()->comment('email');
            $table->date('start_on')->default(NULL)->nullable()->comment('開始日');
            $table->date('end_on')->default('2049/12/31')->nullable()->comment('終了日');
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
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
