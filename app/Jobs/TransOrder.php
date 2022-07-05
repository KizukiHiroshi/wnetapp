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
        $maxcreatedat = $this->getMaxAt('created_at');
        // 管理済履歴と比較する
        $latest_created = $this->getLatest('created');
        if ($maxcreatedat > $latest_created) {
            // より新しければ新テーブルと比較する
            
            // 管理済履歴を更新する
            $this->updateLatest('created', $maxcreatedat);
        }
        // 旧テーブルの更新履歴をチェックする
        $maxupdatedat = $this->getMaxAt('updated_at');
        // 管理済履歴と比較する
        $latest_updated = $this->getLatest('updated');
        if ($maxupdatedat > $latest_updated) {
            // より新しければ新テーブルと比較する
            // 管理済履歴を更新する
            $this->updateLatest('updated', $maxupdatedat);
        }
    }
    // $query->where('created_at', '>', $latest_created)
    // ->orWhere('created_at', 'NULL')
    // ->orWhere('updated_at', '>', $latest_updated)
    // ->orWhere('updated_at', 'NULL');

    private function transCreated($latest_created) {
        // 未管理の旧レコードを得る
        $untreatedrows= DB::connection('sqlsrv')
            ->table('wise_login.■■■■■■■')
            ->where('created_at', '>', $latest_created)
            ->get();
        // 新テーブルに更新登録する
        
        // 新テーブルに登録できる$rowsに加工する
        $rows = $this->getRows($untreatedrows);

        // 新テーブル上に該当レコードがあれば更新する
        // 新テーブル上に該当レコードがなければ登録する
    }

    private function getRows($untreatedrows) {

    }

    private function getMaxAt($mode) {
        $maxat = DB::connection('sqlsrv')
            ->table('wise_login.■■■■■■■')
            ->max($mode);
        return $maxat;
    }

    private function getLatest($mode) {
        $targetcolumn = 'latest_'.$mode;
        $row = DB::table('tablereplacements')
            ->where('systemname', 'TransCompany')
            ->select($targetcolumn)
            ->first();
        $latest = $row->$targetcolumn;
        return $latest;
    }

    private function updateLatest($mode, $value) {
        // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $findvalueset = 'tablereplacements?systemname='.urlencode('TransCompany');
        $findvalueservice = new FindValueService;
        $id = $findvalueservice->findValue($findvalueset, 'id');
        $form['latest_'.$mode] = $value;
        $form['updated_by'] = 'transwnet';
        $excuteprocessservice = new ExcuteProcessService;
        $ret_id = $excuteprocessservice->excuteProcess('tablereplacements', $form, $id);
        if ($ret_id == $id) {
            // ok
        } else {
            // ★どうする？
        }
    }

}
