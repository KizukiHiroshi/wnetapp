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

class TransStockshellRyutu implements ShouldQueue
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
        $systemname = 'TransStockshellRyutu';
        $oldtablename = '２２：流通在庫管理';
        $newtablename = 'ryutu_stockshells';
        while (true) {
            $transrows = $this->getTransRows($systemname, $oldtablename);
            //  $newtablenameを更新する
            $this->updateNewTable($transrows, $newtablename);
            // 管理済履歴を更新する
            $transwnetservice = new TranswnetService;
            $transwnetservice->updateTablereplacement($systemname, $oldtablename);
            //  レコードが無ければexit
            if ($transrows->count() == 0) { break; }
        }
   }

    private function getTransRows($systemname, $oldtablename) {
        // 転記の終わっている日付を取得する
        $transwnetservice = new TranswnetService;
        $latest_created = $transwnetservice->getLatest('created', $systemname);
        $latest_created = date('Y/m/d H:i:s', strtotime($latest_created . '+1 second'));
        $latest_updated = $transwnetservice->getLatest('updated', $systemname);
        $latest_updated = date('Y/m/d H:i:s', strtotime($latest_updated . '+1 second'));
         // 転記の条件を考慮しながら旧テーブルから情報取得する
        $transrows= DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename)
            ->select('在庫主体コード', '棚番号', DB::raw('max(created_at) as created_at'), DB::raw('max(updated_at) as updated_at'))
            ->where('無効ＦＬＧ', '0')
            ->whereRaw("created_at > CONVERT(DATETIME, '".$latest_created."') or updated_at > CONVERT(DATETIME, '".$latest_updated."')")
            ->groupBy('在庫主体コード', '棚番号')
            ->orderByRaw("convert(char,在庫主体コード)+棚番号")
            ->limit(1000)
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
            $rawcompanycode = $transrow->在庫主体コード;
            $companycode = $rawcompanycode == 0 ? '0001' : '5700';
            $businessunitcode = '99999';
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'companies?code='.urlencode($companycode);
            $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
            $company_id = $iddictionary[$foreginkey];
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'businessunit?company_id='.$company_id.'&&code='.urlencode($businessunitcode);
            $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
            $form['businessunit_id'] = $iddictionary[$foreginkey];
            $form['code'] = (strval($transrow->棚番号) == '' ? '0' : mb_convert_kana(trim($transrow->棚番号), "as"));
            $form['name'] = '-';
            $form['remark'] = '';
            $form['updated_at'] = $transrow->updated_at == null ? '2000/01/01' : $transrow->updated_at;
            $form['updated_by'] = 'transrow';
            $form += $transwnetservice->addCreatedToForm($transrow->created_at);
            $foreginkey = $newtablename.'?businessunit_id='.$form['businessunit_id'].'&&code='.urlencode($form['code']);
            $id = $findvalueservice->findValue($foreginkey, 'id');
            $ret_id = $excuteprocessservice->excuteProcess($newtablename , $form, $id); 
        }
    }
}
