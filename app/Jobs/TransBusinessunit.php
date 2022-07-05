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

class TransBusinessunit implements ShouldQueue
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
        $systemname = 'TransBusinessunit';
        $oldtablename = '０１：店舗Ｍ';
        $newtablename = 'businessunits';
        // 未管理の旧レコードを得る
        $untreatedrows = $this->getUntreatedRows($systemname, $oldtablename);
        // 新テーブルに反映する        // 新テーブルに反映する
        $this->updateNewTable($untreatedrows, $newtablename );
        // 管理済履歴を更新する
        $transwnetservice = new TranswnetService;
        $transwnetservice->updateTablereplacement($systemname, $oldtablename);
    }

    private function updateNewTable($untreatedrows, $newtablename ) {
        foreach ($untreatedrows as $untreatedrow) {
            // 個別の転記実体
            $companycode = substr('0000'.strval(intval(intval($untreatedrow->店コード)/100000)), -4);
            if ($companycode == '0000') { $companycode = '0001'; }
            $code = substr('0000'.$untreatedrow->店コード, -5);
            $findvalueservice = new FindValueService;
            // 参照idを確定するためのユニークキーをセットする
            $findvalueset = 'companies?code='.urlencode($companycode);
            $company_id = $findvalueservice->findValue($findvalueset, 'id');
            $form = [];
            $form['company_id'] = $company_id;
            $form['code'] = $code;
            $form['name'] = trim($untreatedrow->店名称);
            $form['name_short'] = trim($untreatedrow->発注店名称);
            $form['postalcode'] = trim($untreatedrow->郵便番号);
            if ($form['postalcode'] == '') { $form['postalcode'] = '000-0000'; }
            $form['address1'] = trim($untreatedrow->住所);
            if ($form['address1'] == '') { $form['address1'] = '未登録'; }
            $form['telno'] = trim($untreatedrow->TEL番号);
            if ($form['telno'] == '') { $form['telno'] = '00-0000-0000'; }
            $form['faxno'] = trim($untreatedrow->FAX番号);
            $form['email'] = trim($untreatedrow->MailAdd);
            $form['updated_at'] = date("Y-m-d H:i:s");
            $form['updated_by'] = 'transwnet';
            $findvalueset = 'businessunits?company_id='.urlencode($company_id).'&&code='.urlencode($code);
            $id = $findvalueservice->findValue($findvalueset, 'id');
            if ($id == 0) {
                $transwnetservice = new TranswnetService;
                $form += $transwnetservice->addCreatedToForm($untreatedrow->created_at);
            }
            $excuteprocessservice = new ExcuteProcessService;
            $ret_id = $excuteprocessservice->excuteProcess($newtablename, $form, $id); 
            // 他のテーブルへも関与する
            // companiesのis_buyerを確認
            $form = [];
            $form['is_buyer'] = 1;
            $form['updated_at'] = date("Y-m-d H:i:s");
            $form['updated_by'] = 'transwnet';
            $ret_id = $excuteprocessservice->excuteProcess('companies', $form, $company_id); 
            // vendor_in_companiesの締め日
            if (trim($untreatedrow->請求締日) <> '31') {
                // 参照idを確定するためのユニークキーをセットする
                $findvalueset = 'vendor_in_companies?company_id='.urlencode($company_id);
                $vendor_in_company_id = $findvalueservice->findValue($findvalueset, 'id');
                if ($vendor_in_company_id == 0) {
                    // 顧客として未登録の企業を登録する
                    $vendor_in_company_id = $this->addCompanyAsBuyer($company_id, $companycode);
                }
                $form = [];
                $form['closingdate_opt'] = trim($untreatedrow->請求締日);
                $form['updated_at'] = $untreatedrow->updated_at;
                $form['updated_by'] = 'transwnet';
                $ret_id = $excuteprocessservice->excuteProcess('vendor_in_companies', $form, $vendor_in_company_id);     
            }
        }
    }

    // 顧客として未登録の企業を登録する
    private function addCompanyAsBuyer($company_id, $companycode) {
        // １２：仕入先Ｍの行取得
        $tgtcompanyrow = $this->getOldrow($companycode);
        $form = [];
        $form['company_id'] = $company_id;
        // 値をそのまま
        $form['pic'] = trim($tgtcompanyrow->先方担当者名);
        $form['telno'] = trim($tgtcompanyrow->電話番号);
        $form['faxno'] = trim($tgtcompanyrow->FAX番号);
        $form['emails'] = trim($tgtcompanyrow->MailAdd);
        $form['tax_rounding_opt'] = trim($tgtcompanyrow->消費税処理);
        // 定型部分
        $form['updated_at'] = date("Y-m-d H:i:s");
        $form['updated_by'] = 'transwnet';
        $transwnetservice = new TranswnetService;
        $form += $transwnetservice->addCreatedToForm($tgtcompanyrow->created_at);
        $excuteprocessservice = new ExcuteProcessService;
        $id = 0;
        $ret_id = $excuteprocessservice->excuteProcess('vendor_in_companies', $form, $id); 
        return $ret_id; 
    }

    // １２：仕入先Ｍの行取得
    private function getOldrow($companycode) {
        $tgtcompanyrow= DB::connection('sqlsrv')
            ->table('wise_login.１２：仕入先Ｍ')
            ->where('仕入先コード', intval($companycode))
            ->first();
        return $tgtcompanyrow;
    }

    // 転記の終わっている日付を取得する
    private function getUntreatedRows($systemname, $oldtablename) {
        $transwnetservice = new TranswnetService;
        $latest_created = $transwnetservice->getLatest('created', $systemname);
        $latest_updated = $transwnetservice->getLatest('updated', $systemname);
        // 転記の条件を考慮しながら旧テーブルから情報取得する
        $untreatedrows= DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename)
            ->where('dele_flg', '1')
            ->where(function($query) use($latest_created, $latest_updated) {
                $query->where('created_at', '>', $latest_created)
                ->orWhere('updated_at', '>', $latest_updated);
            })
            ->orderby('updated_at', 'asc')
            ->get();
        return $untreatedrows;
    }
}
