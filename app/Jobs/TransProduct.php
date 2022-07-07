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


class TransProduct implements ShouldQueue
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
        $systemname = 'TransProduct';
        $oldtablename = '０８：センター商品Ｍ';
        $newtablename = 'products';
        // memory対策
        while (true) {
            // 「$newtablenameのnameの最大値=$knownmaxname」を取得
            // $knownmaxbrand = '';
            // $knownmaxname = '';    
            // $maxid = DB::table('products')->max('id');
            // if ($maxid) {
            //     $knownmaxbrandid= DB::table('products')->where('id', $maxid)->first()->brand_id;
            //     $rawknownmaxname= DB::table('products')->where('id', $maxid)->first()->name;
            //     $rawknownmaxbrand = DB::table('brands')->where('id', $knownmaxbrandid)->first()->name;
            //     $knownmaxbrand = mb_convert_kana($rawknownmaxbrand, "AS");
            //     $knownmaxname = mb_convert_kana($rawknownmaxname, "AS");    
            // }
            //  $oldtablenameから$knownmaxnameより大きい1000レコードを取得
            // $transrow = $this->getTransRowsOld($systemname, $oldtablename, $knownmaxbrand, $knownmaxname);
            $transrows = $this->getTransRows($systemname, $oldtablename);
            //  レコードが無ければexit
            if ($transrows->count() == 0) { break; }
            //  $newtablenameを更新する
            $this->updateNewTable($transrows, $newtablename);
            // 管理済履歴を更新する
            $transwnetservice = new TranswnetService;
            $transwnetservice->updateTablereplacement($systemname, $oldtablename);
        }
    }

    private function getTransRows($systemname, $oldtablename) {
        // 転記の終わっている日付を取得する
        $transwnetservice = new TranswnetService;
        $latest_created = $transwnetservice->getLatest('created', $systemname);
        $latest_updated = $transwnetservice->getLatest('updated', $systemname);
        // 転記の条件を考慮しながら旧テーブルから情報取得する
        $transrows= DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename)
            ->select('メーカー名', '商品名', DB::raw('max(created_at) as created_at'), DB::raw('max(updated_at) as updated_at'))
            ->where('仮本区分', '1')
            ->where(function($query) use($latest_created, $latest_updated) {
                $query->where('created_at', '>', $latest_created)
                ->orWhere('updated_at', '>', $latest_updated);
            })
            ->groupBy('メーカー名', '商品名')
            // ->limit(1000)
            ->get();
        return $transrows;
    }

    private function getTransRowsOld($systemname, $oldtablename, $knownmaxbrand, $knownmaxname) {
        // 転記の終わっている日付を取得する
        $transwnetservice = new TranswnetService;
        $latest_created = $transwnetservice->getLatest('created', $systemname);
        $latest_updated = $transwnetservice->getLatest('updated', $systemname);
        // 転記の条件を考慮しながら旧テーブルから情報取得する
        $transrows= DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename)
            ->select('メーカー名', '商品名', DB::raw('max(created_at) as created_at'), DB::raw('max(updated_at) as updated_at'))
            ->where('仮本区分', '1')
            ->where(function($query) use($knownmaxbrand, $knownmaxname, $latest_created, $latest_updated) {
                $query->where(function($query) use($knownmaxbrand, $knownmaxname) {
                    $query->whereRaw("RTRIM(メーカー名)+RTRIM(商品名) > '".$knownmaxbrand.$knownmaxname."'");
                })->orWhere(function($query) use($latest_created, $latest_updated) {
                    $query->where('created_at', '>', $latest_created)
                    ->orWhere('updated_at', '>', $latest_updated);
                });
            })
            ->orderByRaw("RTRIM(メーカー名)+RTRIM(商品名)")
            ->groupBy('メーカー名', '商品名')
            ->limit(1000)
            ->get();
        return $transrows;
    }

    private function updateNewTable($transrows, $newtablename) {
        $iddictionary = [];   // テーブル参照idリスト
        $getfuriganaservice = new GetFuriganaService;
        foreach ($transrows as $transrow) {
            $form = [];
            $brand = mb_convert_kana(trim($transrow->メーカー名), "a");
            $rawproduct = mb_convert_kana(trim($transrow->商品名), "as");
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'brands?name='.urlencode($brand);
            if (array_key_exists($foreginkey, $iddictionary)) {
                $brand_id = $iddictionary[$foreginkey];
            } else {
                // 未登録の参照を$iddictionaryに追加する
                $findvalueservice = new FindValueService;
                $brand_id = $findvalueservice->findValue($foreginkey, 'id');
                if ($brand_id == 0) {
                    $brand_id = $this->registBrand($brand);
                }
                $iddictionary[$foreginkey] = $brand_id;
            }
            $form['brand_id'] = $brand_id;
            $form['name'] = $rawproduct;
            $form['name_kana'] = $getfuriganaservice->GetFurigana($rawproduct);
            $form['url'] = '';
            $form['image'] = '';
            $form['updated_at'] = $transrow->updated_at;
            $form['updated_by'] = 'transwnet';
            $findvalueset = $newtablename.'?brand_id='.urlencode(strval($brand_id)).'&&name='.urlencode($form['name']);
            $id = $findvalueservice->findValue($findvalueset, 'id');
            if ($id == 'many') {
                continue;
            } elseif ($id == 0) {
                $transwnetservice = new TranswnetService;
                $form += $transwnetservice->addCreatedToForm($transrow->created_at);
            }
            $excuteprocessservice = new ExcuteProcessService;
            $ret_id = $excuteprocessservice->excuteProcess($newtablename , $form, $id); 
        }
    }

    private function registBrand($brand) {
        $getfuriganaservice = new GetFuriganaService;
        $transwnetservice = new TranswnetService;
        $form = [];
        $form['name'] = $brand;
        $form['name_kana'] = $getfuriganaservice->GetFurigana($brand);
        $form['url'] = '';
        $form['image'] = '';
        $form['updated_at'] = date("Y-m-d H:i:s");
        $form['updated_by'] = 'transwnet';
        $form += $transwnetservice->addCreatedToForm(NULL);
        $excuteprocessservice = new ExcuteProcessService;
        $id = 0;
        $brand_id = $excuteprocessservice->excuteProcess('brands' , $form, $id);
        return $brand_id;
    }
}
