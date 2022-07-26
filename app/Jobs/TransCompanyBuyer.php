<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Services\Database\ExcuteProcessService;
use App\Services\Database\FindValueService;
use App\Services\Transwnet\TranswnetService;

class TransCompanyBuyer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(){
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 旧テーブルの登録履歴をチェックする
        // 管理済の日付を取得する
        $systemname = 'TransCompanyBuyer';
        $oldtablename = '１２：仕入先Ｍ';
        $newtablename = 'buyer_in_companies';
        // 未管理の旧レコードを得る
        $untreatedrows = $this->getUntreatedRows($systemname, $oldtablename);
        // 新テーブルに反映する
        $this->updateNewTable($untreatedrows, $newtablename);
        // 管理済履歴を更新する
        $transwnetservice = new TranswnetService;
        $transwnetservice->updateTablereplacement($systemname, $oldtablename);
    }

    private function getUntreatedRows($systemname, $oldtablename) {
        // 転記の終わっている日付を取得する
        $transwnetservice = new TranswnetService;
        $latest_created = $transwnetservice->getLatest('created', $systemname);
        $latest_updated = $transwnetservice->getLatest('updated', $systemname);
        // 転記の条件を考慮しながら旧テーブルから情報取得する
        $buyercompanies = DB::connection('sqlsrv')
            ->table('wise_login.１３：店舗発注明細')
            ->selectRaw('CONVERT(int, 店コード / 100000) AS code')->groupByRaw('CONVERT(int, 店コード / 100000)');
        $untreatedrows= DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename.' as rawcompany')
            ->JoinSub($buyercompanies, 'buyer', 'rawcompany.仕入先コード', 'buyer.code')
            ->where(function($query) use($latest_created, $latest_updated) {
                $query->where('created_at', '>', $latest_created)
                ->orWhere('updated_at', '>', $latest_updated);
            })
            ->get();
        return $untreatedrows;
    }

    private function updateNewTable($untreatedrows, $newtablename) {
        foreach ($untreatedrows as $untreatedrow) {
            // 個別の転記実体
            $code = substr('0000'.$untreatedrow->仕入先コード, -4);
            if ($code == '0000') { $code = '0001'; }
            $findvalueservice = new FindValueService;
            $form = [];
            // 参照idを確定するためのユニークキーをセットする
            $findvalueset = 'companies?code='.urlencode($code);
            $company_id = $findvalueservice->findValue($findvalueset, 'id');
            // company_id
            $form['company_id'] = $company_id;
            // department
            $form['department'] = '';
            // position
            $form['position'] = '';
            // pic
            $form['pic'] = trim($untreatedrow->先方担当者名);
            // telno
            $form['telno'] = trim($untreatedrow->電話番号);
            // faxno
            $form['faxno'] = trim($untreatedrow->FAX番号);
            // emails
            $form['emails'] = trim($untreatedrow->MailAdd);
            // getorderdayofweek
            // getordertimeonday
            // shippingdayofweek
            // freeshippingquantity
            // freeshippingamount
            // price_rounding_opt
            $findvalueset = 'option_choices?variablename_system=price_rounding_opt&&no=2';
            $price_rounding_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['price_rounding_opt'] = $price_rounding_opt_id;
            // is_mustsenddirect
            $form['is_mustsenddirect'] = '1';
            // shippinggremarks
            // closingdate_opt
            $closingdate = trim($untreatedrow->締日);
            if ($closingdate == '0') { $closingdate = '31'; }
            $findvalueset = 'option_choices?variablename_system=closingdate_opt&&valuename_system='.$closingdate;
            $closingdate_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['closingdate_opt'] = $closingdate_opt_id;
            // duedate_opt
            $findvalueset = 'option_choices?variablename_system=duedate_opt&&valuename_system=31';
            $duedate_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['duedate_opt'] = $duedate_opt_id;
             // tax_rounding_opt
            $findvalueset = 'option_choices?variablename_system=tax_rounding_opt&&no=1';
            $tax_rounding_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['tax_rounding_opt'] = $tax_rounding_opt_id;
            // shiftoftax_opt
            $findvalueset = 'option_choices?variablename_system=shiftoftax_opt&&no=2';
            $shiftoftax_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['shiftoftax_opt'] = $shiftoftax_opt_id;
            // paymentmethod_opt
            $findvalueset = 'option_choices?variablename_system=paymentmethod_opt&&no=1';
            $paymentmethod_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['paymentmethod_opt'] = $paymentmethod_opt_id;
            // accountsreceivablebalance
            // creditlimit
            // company_id_2nd
            // bankname
            // bankname_kana
            // bankbranchno
            // bankbranchname
            // bankbranchname_kana
            // bankdeposittype_opt
            $findvalueset = 'option_choices?variablename_system=bankdeposittype_opt&&no=1';
            $bankdeposittype_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['bankdeposittype_opt'] = $bankdeposittype_opt_id;           
            // bankaccountnumber
            // bankaccountname
            // bankaccountname_kana
            // is_buyerpaysfee
            // getorderpriority
            // getordermethod_opt
            $findvalueset = 'option_choices?variablename_system=getordermethod_opt&&no=2';
            $getordermethod_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['getordermethod_opt'] = $getordermethod_opt_id;
            // need_specifiedslip
            // need_shipbyorder
            // is_unshipedcancel
            // maxshipingdays
            // remarks
            // 定型部分
            $form['updated_at'] = $untreatedrow->updated_at;
            $form['updated_by'] = 'transwnet';
            // 新テーブルのidを確定するためのユニークキーをセットする
            $findvalueset = $newtablename.'?company_id='.urlencode($form['company_id']);
            $id = $findvalueservice->findValue($findvalueset, 'id');
            // 新規の場合は'created_'も設定する
            if ($id == 0) {
                $transwnetservice = new TranswnetService;
                $form += $transwnetservice->addCreatedToForm($untreatedrow->created_at);
            }
            $excuteprocessservice = new ExcuteProcessService;
            $ret_id = $excuteprocessservice->excuteProcess($newtablename, $form, $id);  
        }
    }
}
