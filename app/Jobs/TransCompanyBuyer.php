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
        $untreatedrows= DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename)
            ->where(function($query) use($latest_created, $latest_updated) {
                $query->where('created_at', '>', $latest_created)
                    ->orWhere('updated_at', '>', $latest_updated);
            })
            ->get();
        return $untreatedrows;
    }

    private function updateNewTable($untreatedrows, $newtablename) {
        foreach ($untreatedrows as $untreatedrow) {
            if (intval($untreatedrow->発注曜日) !== 0) {
                continue;
            }
            // 個別の転記実体
            $code = substr('0000'.$untreatedrow->仕入先コード, -4);
            $findvalueservice = new FindValueService;
            // 参照idを確定するためのユニークキーをセットする
            $findvalueset = 'companies?code='.$code;
            $company_id = $findvalueservice->findValue($findvalueset, 'id');
            $form = [];
            $form['company_id'] = $company_id;
            // 値をそのまま
            $form['pic'] = trim($untreatedrow->先方担当者名);
            $form['telno'] = trim($untreatedrow->電話番号);
            $form['faxno'] = trim($untreatedrow->FAX番号);
            $form['emails'] = trim($untreatedrow->MailAdd);
            $form['closingdate_opt'] = trim($untreatedrow->締日);
            $form['tax_rounding_opt'] = trim($untreatedrow->消費税処理);
            // 定型部分
            $form['updated_at'] = $untreatedrow->updated_at;
            $form['updated_by'] = 'transwnet';
            // 新テーブルのidを確定するためのユニークキーをセットする
            $findvalueset = $newtablename.'?company_id='.$form['company_id'];
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
