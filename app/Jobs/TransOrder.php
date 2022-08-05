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

class TransOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $systemname = 'TransOrder';
        $oldtablename = '１３：店舗発注明細';
        $newtablename1 = 'order_details';
        $newtablename2 = 'order_labels';
        while (true) {
            $maxid = $this->getKnownshopandcode($systemname);
            $transrows = $this->getTransRows($systemname, $oldtablename, $maxid);
            //  $newtablenameを更新する
            $this->updateNewTable($transrows, $newtablename1, $newtablename2);
            // 管理済履歴を更新する
            $transwnetservice = new TranswnetService;
            $transwnetservice->updateTablereplacement($systemname, $oldtablename, $maxid);
            //  レコードが無ければexit
            if ($transrows->count() == 0) {
                break;
           }
        }
    }


    private function updateNewTable($transrows, $newtablename, $newtablename2) {
        $iddictionary = [];   // テーブル参照idリスト
        $addiddictionaryservice = new AddIddictionaryService;
        $findvalueservice = new FindValueService;
        $transwnetservice = new TranswnetService;
        $excuteprocessservice = new ExcuteProcessService;
        $orderlabel_id = null;
        $regularprice_total = 0;
        $price_total = 0;
        $tax_total = 0;
        $detail_nocnt = 1;
        foreach ($transrows as $transrow) {
            $form = [];
            $shopcode = $transrow->店コード;
            $separatedshopcode = $transwnetservice->separateRawShopcode($shopcode);
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'companies?code='.urlencode($separatedshopcode['companycode']);
            $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
            $company_id = $iddictionary[$foreginkey];
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'businessunit?company_id='.$company_id.'&&code='.urlencode($separatedshopcode['businessunitcode']);
            $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
            $businessunit_id = $iddictionary[$foreginkey];

        // orderlabel_id
        // detail_no
        // productitem_id
        // regularprice
        // price
        // quantity
        // taxrate
        // remark
        // available_quantity
        // is_completed
        // transaction
        // old13
        // old14

        // order_no
// order_on
// order__company_id
// order__businessunit_id
// ordered_by
// getorder__company_id
// getorder__businessunit_id
// need_deliverydate
// due_date
// regularprice_total
// price_total
// tax_total
// delivery__businessunit_id
// is_recieved
// published_on
// remark
// is_completed
// transaction
// old13id
// old14id
            $form = [];
            $shopcode = $transrow->店コード;
            $separatedshopcode = $transwnetservice->separateRawShopcode($shopcode);
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

    private function getTransRows($systemname, $oldtablename, $maxid) {
        // 転記の終わっている日付を取得する
        // $transwnetservice = new TranswnetService;
        // $latest_created = $transwnetservice->getLatest('created', $systemname);
        // $latest_created = date('Y/m/d H:i:s', strtotime($latest_created . '+1 second'));
        // $latest_updated = $transwnetservice->getLatest('updated', $systemname);
        // $latest_updated = date('Y/m/d H:i:s', strtotime($latest_updated . '+1 second'));
        // 転記の条件を考慮しながら旧テーブルから情報取得する
        $transrows= DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename)
            // ->whereRaw("created_at > CONVERT(DATETIME, '".$latest_created."') or updated_at > CONVERT(DATETIME, '".$latest_updated."')")
            // 初期設定用：登録済みのコード取得
            ->whereRaw("(convert(int, 店コード/100000) <= 9 or convert(int, 店コード/100000) = 5700 or convert(int, 店コード/100000) = 8990)")
            ->whereRaw("(発生ＩＤ > ".$maxid.")")
            ->orderByRaw("発生ＩＤ")
            ->limit(1000)
            ->get();
        return $transrows;
    }

    private function getKnownshopandcode($systemname) {
        $row = DB::table('tablereplacements')
            ->where('systemname', $systemname)
            ->select('maxvalue')->first();
        $maxid = $row->maxvalue;
        return $maxid;
    }

}
