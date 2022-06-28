<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBuyerincompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyer_in_companies', function (Blueprint $table){
            $table->id()->comment('id');
            $table->foreignId('company_id')->comment('企業名')->references('id')->on('companies');
            $table->string('department')->comment('担当部署', 30)->nullable();
            $table->string('position')->comment('担当者役職', 30)->nullable();
            $table->string('pic')->comment('担当者', 20)->nullable();
            $table->string('telno')->comment('電話', 14)->nullable();
            $table->string('faxno')->comment('FAX', 14)->nullable();
            $table->string('emails')->comment('連絡先アドレス', 200)->nullable();
            $table->string('getorderdayofweek')->comment('受注曜日', 7)->default('0')->nullable();
            $table->time('getordertimeonday')->comment('当日受注時間制限')->nullable();
            $table->string('shippingdayofweek')->comment('出荷曜日', 7)->default('0')->nullable();
            $table->integer('freeshippingquantity')->comment('無料出荷数量')->nullable();
            $table->integer('freeshippingamount')->comment('無料出荷下代')->nullable();
            $table->tinyInteger('price_rounding_opt')->comment('単価端数処理')->default(0)->nullable();
            $table->boolean('is_mustsenddirect')->comment('直送要')->default(0)->nullable();
            $table->string('shippinggremarks')->comment('出荷条件備考', 200)->nullable();
            $table->tinyInteger('closingdate_opt')->comment('締め日')->default(31)->nullable();
            $table->tinyInteger('tax_rounding_opt')->comment('税端数処理')->default(0)->nullable();
            $table->tinyInteger('shiftoftax_opt')->comment('税転嫁')->nullable();
            $table->tinyInteger('paymentmethod_opt')->comment('入金方法')->nullable();
            $table->integer('accountsreceivablebalance')->comment('売掛残高')->nullable();
            $table->string('bankname')->comment('振込元銀行名', 20)->nullable();
            $table->string('bankname_kana')->comment('銀行名フリガナ', 20)->nullable();
            $table->string('bankbranchno')->comment('取引支店番号', 5)->nullable();
            $table->string('bankbranchname')->comment('銀行本支店名', 20)->nullable();
            $table->string('bankbranchname_kana')->comment('支店名フリガナ', 20)->nullable();
            $table->tinyInteger('bankdeposittype_opt')->comment('預金種類')->nullable();
            $table->string('bankaccountnumber')->comment('口座番号', 8)->nullable();
            $table->string('bankaccountname')->comment('口座名義', 30)->nullable();
            $table->string('bankaccountname_kana')->comment('口座名義フリガナ', 30)->nullable();
            $table->boolean('is_buyerpaysfee')->comment('手数料先方負担')->default(1)->nullable();
            $table->integer('getorderpriority')->comment('受注優先順位')->default(9999)->nullable();
            $table->tinyInteger('getordermethod_opt')->comment('受注方法')->nullable();
            $table->string('remarks')->comment('備考', 200)->nullable();
            $table->date('start_on')->comment('開始日')->default(NULL)->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by')->comment('作成者', 12);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by')->comment('更新者', 12);
        });
        DB::statement("alter table wnetdb_test.buyer_in_companies comment '顧客先企業';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buyer_in_companies');
    }
}
