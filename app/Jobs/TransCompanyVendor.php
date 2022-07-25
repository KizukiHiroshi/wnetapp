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

class TransCompanyVendor implements ShouldQueue
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
        $variablename_system = 'TransCompanyVendor';
        $oldtablename = '１２：仕入先Ｍ';
        $newtablename = 'vendor_in_companies';
        // 未管理の旧レコードを得る
        $untreatedrows = $this->getUntreatedRows($variablename_system, $oldtablename);
        // 新テーブルに反映する
        $this->updateNewTable($untreatedrows, $newtablename);
        // 管理済履歴を更新する
        $transwnetservice = new TranswnetService;
        $transwnetservice->updateTablereplacement($variablename_system, $oldtablename);
    }

    private function getUntreatedRows($variablename_system, $oldtablename) {
        // 転記の終わっている日付を取得する
        $transwnetservice = new TranswnetService;
        $latest_created = $transwnetservice->getLatest('created', $variablename_system);
        $latest_updated = $transwnetservice->getLatest('updated', $variablename_system);
        // 転記の条件を考慮しながら旧テーブルから情報取得する
        $vendorcompanies = DB::connection('sqlsrv')
            ->table('wise_login.１３：店舗発注明細')
            ->select('仕入先コード as code')->groupBy('仕入先コード');
        $untreatedrows= DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename.' as rawcompany')
            ->JoinSub($vendorcompanies, 'vendor', 'rawcompany.仕入先コード', 'vendor.code')
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
            // 参照idを確定するためのユニークキーをセットする
            $findvalueset = 'companies?code='.urlencode($code);
            $company_id = $findvalueservice->findValue($findvalueset, 'id');
            $form = [];
            $form['company_id'] = $company_id;
            // 値をそのまま
            $form['department'] = '';
            $form['position'] = '';
            $form['pic'] = trim($untreatedrow->先方担当者名);
            $form['telno'] = trim($untreatedrow->電話番号);
            $form['faxno'] = trim($untreatedrow->FAX番号);
            $form['emails'] = trim($untreatedrow->MailAdd);
            $form['orderdayofweek'] = trim($untreatedrow->発注曜日);
            $form['ordertimeonday'] = null;           
            $form['arrivaldayofweek'] = trim($untreatedrow->入荷曜日);
            $form['freeshippingquantity'] = trim($untreatedrow->無料入荷数量);
            $form['freeshippingamount'] = trim($untreatedrow->無料入荷下代);
            $findvalueset = 'option_choices?variablename_system=price_rounding_opt&&no=2';
            $price_rounding_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['price_rounding_opt'] = $price_rounding_opt_id;
            $form['is_cansenddirect'] = trim($untreatedrow->直送可);
            $form['shippinggremarks'] = trim($untreatedrow->出荷条件);
            $closingdate = trim($untreatedrow->締日);
            if ($closingdate == '0') { $closingdate = '31'; }
            $findvalueset = 'option_choices?variablename_system=closingdate_opt&&valuename_system='.$closingdate;
            $closingdate_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['closingdate_opt'] = $closingdate_opt_id;
            $findvalueset = 'option_choices?variablename_system=tax_rounding_opt&&no=1';
            $tax_rounding_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['tax_rounding_opt'] = $tax_rounding_opt_id;
            $findvalueset = 'option_choices?variablename_system=shiftoftax_opt&&no=2';
            $shiftoftax_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['shiftoftax_opt'] = $shiftoftax_opt_id;
            $findvalueset = 'option_choices?variablename_system=paymentmethod_opt&&no=1';
            $paymentmethod_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['paymentmethod_opt'] = $paymentmethod_opt_id;
            // accountspayable
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
            $form['is_vendorpaysfee'] = '1';
            $form['orderpriority'] = trim($untreatedrow->発注優先順位);;
            $findvalueset = 'option_choices?variablename_system=ordermethod_opt&&no='.trim($untreatedrow->発注方法);
            $ordermethod_opt_id = $findvalueservice->findValue($findvalueset, 'id');
            $form['ordermethod_opt'] = $ordermethod_opt_id;
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
