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
            $table->string('department', 30)->comment('担当部署')->nullable();
            $table->string('position', 30)->comment('担当者役職')->nullable();
            $table->string('pic', 20)->comment('担当者')->nullable();
            $table->string('telno', 14)->comment('電話')->nullable();
            $table->string('faxno', 14)->comment('FAX')->nullable();
            $table->string('emails', 200)->comment('連絡先アドレス')->nullable();
            $table->string('getorderdayofweek', 7)->comment('受注曜日')->default('0')->nullable();
            $table->time('getordertimeonday')->comment('当日受注時間制限')->nullable();
            $table->string('shippingdayofweek', 7)->comment('出荷曜日')->default('0')->nullable();
            $table->integer('freeshippingquantity')->comment('無料出荷数量')->nullable();
            $table->integer('freeshippingamount')->comment('無料出荷下代')->nullable();
            $table->bigInteger('price_rounding_opt')->comment('単価端数処理')->default(0)->nullable();
            $table->boolean('is_mustsenddirect')->comment('直送要')->default(0)->nullable();
            $table->string('shippinggremarks', 200)->comment('出荷条件備考')->nullable();
            $table->bigInteger('closingdate_opt')->comment('締め日')->default(31)->nullable();
            $table->bigInteger('duedate_opt')->comment('締後支払日数')->default(31)->nullable();
            $table->bigInteger('tax_rounding_opt')->comment('税端数処理')->default(0)->nullable();
            $table->bigInteger('shiftoftax_opt')->comment('税転嫁')->nullable();
            $table->bigInteger('paymentmethod_opt')->comment('入金方法')->nullable();
            $table->integer('accountsreceivablebalance')->comment('売掛残高')->nullable();
            $table->integer('creditlimit')->comment('与信限度額')->nullable();
            $table->foreignId('company_id_2nd')->comment('請求先企業');
            $table->string('bankname', 20)->comment('振込元銀行名')->nullable();
            $table->string('bankname_kana', 20)->comment('銀行名フリガナ')->nullable();
            $table->string('bankbranchno', 5)->comment('取引支店番号')->nullable();
            $table->string('bankbranchname', 20)->comment('銀行本支店名')->nullable();
            $table->string('bankbranchname_kana', 20)->comment('支店名フリガナ')->nullable();
            $table->bigInteger('bankdeposittype_opt')->comment('預金種類')->nullable();
            $table->string('bankaccountnumber', 8)->comment('口座番号')->nullable();
            $table->string('bankaccountname', 30)->comment('口座名義')->nullable();
            $table->string('bankaccountname_kana', 30)->comment('口座名義フリガナ')->nullable();
            $table->boolean('is_buyerpaysfee')->comment('手数料先方負担')->default(1)->nullable();
            $table->integer('getorderpriority')->comment('受注優先順位')->default(9999)->nullable();
            $table->bigInteger('getordermethod_opt')->comment('受注方法')->nullable();
            $table->boolean('need_specifiedslip')->comment('指定伝票出荷')->default(0)->nullable();
            $table->boolean('need_shipbyorder')->comment('発注伝票毎出荷')->default(0)->nullable();
            $table->boolean('is_unshipedcancel')->comment('未出荷分取消')->default(0)->nullable();
            $table->integer('maxshipingdays')->comment('最大出荷日数')->default(0)->nullable();
            $table->string('remarks', 200)->comment('備考')->nullable();
            $table->date('start_on')->comment('開始日')->default('2000/01/01')->nullable();
            $table->date('end_on')->comment('終了日')->default('2049/12/31')->nullable();
            $table->softDeletes()->comment('削除日時');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('created_by', 12)->comment('作成者');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->string('updated_by', 12)->comment('更新者');
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
