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


class TransCompany implements ShouldQueue
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
        $systemname = 'TransCompany';
        $oldtablename = '１２：仕入先Ｍ';
        $newtablename = 'companies';
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
            if (intval($untreatedrow->仕入先コード) == 0) {
                continue;
            }
            $form = [];
            $form['code'] = substr('0000'.$untreatedrow->仕入先コード, -4);
            $form['name'] = trim($untreatedrow->仕入先名);
            $form['name_kana'] = trim($untreatedrow->仕入先名カナ);
            $name_short = str_replace('㈱', '', $form['name']);
            $name_short = str_replace('㈲', '', $name_short);
            $form['name_short'] = mb_substr($name_short, 0, 10);
            $form['postalcode'] = trim($untreatedrow->郵便番号);
            $form['address1'] = trim($untreatedrow->住所１);
            $form['address2'] = trim($untreatedrow->住所２);
            $form['telno'] = trim($untreatedrow->電話番号);
            $form['faxno'] = trim($untreatedrow->FAX番号);
            $form['email'] = trim($untreatedrow->MailAdd);
            if (intval($untreatedrow->発注曜日) !== 0) {
                $form['has_businessunit'] = 0;
                $form['is_buyer'] = 0;
                $form['is_vendor'] = 1;
            } else {
                $form['has_businessunit'] = 1;
                $form['is_buyer'] = 1;
                $form['is_vendor'] = 0;    
            }
            $form['can_work'] = 0;
            $form['updated_at'] = $untreatedrow->updated_at;
            $form['updated_by'] = 'transwnet';
            $findvalueset = $newtablename.'?code='.$form['code'];
            $findvalueservice = new FindValueService;
            $id = $findvalueservice->findValue($findvalueset, 'id');
            if ($id == 0) {
                $transwnetservice = new TranswnetService;
                $form += $transwnetservice->addCreatedToForm($untreatedrow->created_at);
            }
            $excuteprocessservice = new ExcuteProcessService;
            $ret_id = $excuteprocessservice->excuteProcess($newtablename , $form, $id); 
        }
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
}
