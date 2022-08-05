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
use App\Services\Database\AddIddictionaryService;
use App\Services\Transwnet\TranswnetService;

class TransStockRyutu implements ShouldQueue
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
        $systemname = 'TransStockRyutu';
        $oldtablename = '２２：流通在庫管理';
        $newtablename = 'ryutu_stocks';
        while (true) {
            $transrows = $this->getTransRows($systemname, $oldtablename);
            //  レコードが無ければexit
            //  $newtablenameを更新する
            $this->updateNewTable($transrows, $newtablename);
            // 管理済履歴を更新する
            $transwnetservice = new TranswnetService;
            $transwnetservice->updateTablereplacement($systemname, $oldtablename);
            if ($transrows->count() == 0) {
                break;
           }
      }
   }

    private function getTransRows($systemname, $oldtablename) {
        // 転記の終わっている日付を取得する
        $transwnetservice = new TranswnetService;
        $latest_created = $transwnetservice->getLatest('created', $systemname);
        $latest_created = date('Y/m/d H:i:s', strtotime($latest_created . '+1 second'));
        $latest_updated = $transwnetservice->getLatest('updated', $systemname);
        $latest_updated = date('Y/m/d H:i:s', strtotime($latest_updated . '+1 second'));
        // 初期設定用：登録済みのコード取得
        $knownshopandcode  = $this->getKnownshopandcode();
        // 転記の条件を考慮しながら旧テーブルから情報取得する
        $transrows= DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename)
            ->where('無効ＦＬＧ', '0')
            ->whereRaw("created_at > CONVERT(DATETIME, '".$latest_created."') or updated_at > CONVERT(DATETIME, '".$latest_updated."')")
            // 初期設定用：登録済みのコード取得
            ->whereRaw("convert(float,rtrim(convert(char, 在庫主体コード))+'.'+ＪＡＮコード) > convert(float,'".$knownshopandcode."')")
            // ->whereRaw("convert(float,rtrim(convert(char, 在庫主体コード))+'.'+ＪＡＮコード) > convert(float,'5700.4005401159995')")
            ->orderByRaw("在庫主体コード, ＪＡＮコード")
            // ->limit(1000)
            ->get();
        return $transrows;
    }

    private function updateNewTable($transrows, $newtablename) {
        $iddictionary = [];   // テーブル参照idリスト
        $addiddictionaryservice = new AddIddictionaryService;
        $findvalueservice = new FindValueService;
        $transwnetservice = new TranswnetService;
        $excuteprocessservice = new ExcuteProcessService;
        foreach ($transrows as $transrow) {
            $form = [];
            $shopcode = $transrow->在庫主体コード;
            $separatedshopcode = $transwnetservice->separateRawShopcode($shopcode.'99999');
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'companies?code='.urlencode($separatedshopcode['companycode']);
            $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
            $company_id = $iddictionary[$foreginkey];
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'businessunit?company_id='.$company_id.'&&code='.urlencode($separatedshopcode['businessunitcode']);
            $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
            $form['businessunit_id'] = $iddictionary[$foreginkey];
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'productitems?code='.trim($transrow->ＪＡＮコード);
            $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
            $form['productitem_id'] = $iddictionary[$foreginkey];
            if ($form['productitem_id'] == NULL) { continue; }
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'ryutu_stockshells?businessunit_id='.$form['businessunit_id'].'&&code='.urlencode(mb_convert_kana(trim($transrow->棚番号), "as"));
            $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
            $form['ryutu_stockshell_id'] = $iddictionary[$foreginkey];
            $form['ryutu_stockshellno'] = $transrow->棚内順 == NULL ? 0 : $transrow->棚内順;
            $form['ryutu_stockshell_id_2nd'] = $form['ryutu_stockshell_id'];
            $form['ryutu_stockshellno2'] = $form['ryutu_stockshellno'];
            $form['currentstock'] = $transrow->現在庫 == NULL ? 0 : $transrow->現在庫;
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値) ※2=通常品
            $foreginkey = 'option_choices?variablename_system='.urlencode(strval('stockstatus_opt')).'&&no='.urlencode('2');
            $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
            $form['stockstatus_opt'] = $iddictionary[$foreginkey];
            $form['is_autoreorder'] = $transrow->発注条件 == 1 ? 1 : 0;
            $form['reorderpoint'] = $transrow->発注点 == NULL ? 0 : $transrow->発注点;
            $form['maxstock'] = $transrow->上限在庫 == NULL ? 0 : $transrow->上限在庫;
            $form['stockupdeted_on'] = $transrow->在庫数更新日 == NULL ? '2000/01/01 00:00:00' : $transrow->在庫数更新日;;
            $form['remark'] = '';
            $form['updated_at'] = $transrow->updated_at == NULL ?  time() : $transrow->updated_at;
            $form['updated_by'] = 'transrow';
            $form += $transwnetservice->addCreatedToForm($transrow->created_at);
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = $newtablename.'?businessunit_id='.$form['businessunit_id'].'&&productitem_id='.$form['productitem_id'];
            $id = $findvalueservice->findValue($foreginkey, 'id');
            $ret_id = $excuteprocessservice->excuteProcess($newtablename , $form, $id); 
        }
    }

    private function getKnownshopandcode() {
        $maxid = DB::table('ryutu_stocks')->max('id');
        if (!$maxid) { return ''; } 
        $row = DB::table('ryutu_stocks')->where('id', $maxid)->first();
        $businessunit_id = $row->businessunit_id;
        $productitem_id = $row->productitem_id;
        $row = DB::table('businessunits')->where('id', $businessunit_id)->first();
        $businessunitcode = $row->code;
        $company_id = $row->company_id;
        $companycode = DB::table('companies')->where('id', $company_id)->first()->code;
        if ($companycode == '0001') { $companycode = '0000'; }
        $shopcode = strval(intval($companycode)*100000 + intval($businessunitcode));
        $code = DB::table('productitems')->where('id', $productitem_id)->first()->code;
        $knownshopandcode = $shopcode.'.'.$code;
        return $knownshopandcode;
    }
}
