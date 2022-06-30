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
use App\Services\Api\GetFuriganaService;


class TransBrand implements ShouldQueue
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
        $systemname = 'TransBrand';
        $oldtablename = '０８：センター商品Ｍ';
        $newtablename = 'brands';
        // 未管理の旧レコードを得る
        $untreatedrows = $this->getUntreatedRows($systemname, $oldtablename);
        // 新テーブルに反映する
        $this->updateNewTable($untreatedrows, $newtablename );
        // 管理済履歴を更新する
        $transwnetservice = new TranswnetService;
        $transwnetservice->updateTablereplacement($systemname, $oldtablename);
    }

    private function updateNewTable($untreatedrows, $newtablename ) {
        $getfuriganaservice = new GetFuriganaService;
        foreach ($untreatedrows as $untreatedrow) {
            $form = [];
            $rawbrand = mb_convert_kana(trim($untreatedrow->メーカー名), "A");
            $form['name'] = $rawbrand;
            $brand = $getfuriganaservice->GetFurigana($rawbrand.'を');
            $brand = mb_substr($brand, 0, -1);
            $form['name_kana'] = $brand;
            $form['url'] = '';
            $form['image'] = '';
            $form['updated_at'] = $untreatedrow->updated_at;
            $form['updated_by'] = 'transwnet';
            $findvalueset = $newtablename.'?name='.$form['name'];
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
            ->select('メーカー名', DB::raw('max(created_at) as created_at'), DB::raw('max(updated_at) as updated_at'))
            ->where('仮本区分', '1')
            ->where(function($query) use($latest_created, $latest_updated) {
                $query->where('created_at', '>', $latest_created)
                ->orWhere('updated_at', '>', $latest_updated);
            })
            ->groupBy('メーカー名')
            ->get();
        return $untreatedrows;
    }
}
