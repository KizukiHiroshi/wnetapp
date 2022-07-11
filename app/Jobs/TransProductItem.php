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
use App\Services\Database\AddIddictionarService;
use App\Services\Transwnet\TranswnetService;
use App\Services\Api\GetFuriganaService;


class TransProductItem implements ShouldQueue
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
        $systemname = 'TransProductItem';
        $oldtablename = '０８：センター商品Ｍ';
        $newtablename = 'productitems';
        while (true) {
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
        $latest_created = date('Y/m/d H:i:s', strtotime($latest_created . '+1 second'));
        $latest_updated = $transwnetservice->getLatest('updated', $systemname);
        $latest_updated = date('Y/m/d H:i:s', strtotime($latest_updated . '+1 second'));
         // 転記の条件を考慮しながら旧テーブルから情報取得する
        $transrows= DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename)
            ->where('仮本区分', '1')
            ->where(function($query) use($latest_created, $latest_updated) {
                $query->where('created_at', '>', $latest_created)
                        ->orWhere('updated_at', '>', $latest_updated);
            })
            ->orderBy('ＪＡＮコード')
            ->limit(1000)
            ->get();
        return $transrows;
    }

    private function updateNewTable($transrows, $newtablename) {
        $iddictionary = [];   // テーブル参照idリスト
        $getfuriganaservice = new GetFuriganaService;
        $addiddictionarservice = new AddIddictionarService;
        $findvalueservice = new FindValueService;
        $excuteprocessservice = new ExcuteProcessService;
        foreach ($transrows as $transrow) {
            $form = [];
            $brand = mb_convert_kana(trim($transrow->メーカー名), "a");
            $product = mb_convert_kana(trim($transrow->商品名), "as");
            $rawitem = trim(mb_convert_kana(trim($transrow->規格), "as"));
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'brands?name='.urlencode($brand);
            $iddictionary = $addiddictionarservice->addIddictionary($iddictionary, $foreginkey);
            $brand_id= $iddictionary[$foreginkey];
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $foreginkey = 'products?brand_id='.urlencode(strval($brand_id)).'&&name='.urlencode($product);
            $iddictionary = $addiddictionarservice->addIddictionary($iddictionary, $foreginkey);
            $form['product_id'] = $iddictionary[$foreginkey];
            $form['code'] = trim($transrow->ＪＡＮコード);
            $form['jancode'] = $this->judgeJancode($transrow);
            $form['prdcode'] = trim($transrow->商品コード);
            $form['name'] = ($rawitem == '' ? '-' : $rawitem);
            $form['name_kana'] = $getfuriganaservice->GetFurigana($rawitem);
            $form['color'] = '';
            $form['size'] = '';
            $form['is_janprinted'] = ($transrow->ＪＡＮ区分 == 1 ? 1 : 0);
            $foreginkey = 'option_choices?variablename_systrem='.urlencode(strval('pricelabel_opt')).'&&no='.urlencode(strval($transrow->値札区分));
            $iddictionary = $addiddictionarservice->addIddictionary($iddictionary, $foreginkey);
            $form['pricelabel_opt'] = $iddictionary[$foreginkey];
            $form['unit'] = $transrow->ケース入り数;
            $foreginkey = 'option_choices?variablename_systrem='.urlencode(strval('unitname_opt')).'&&valuename='.urlencode(strval(trim($transrow->単位名称)));
            $iddictionary = $addiddictionarservice->addIddictionary($iddictionary, $foreginkey);
            $form['unitname_opt'] = $iddictionary[$foreginkey];
            $form['regularprice'] = $transrow->販売単価1;
            $form['regularprice_2nd'] = $transrow->販売単価2;
            $form['start_2nd_on'] = $transrow->単価2実施日;
            $form['url'] = '';
            $form['image'] = '';
            $form['remark'] = '';
            $form['created_at'] = $transrow->本登録日;
            $form['created_by'] = trim($transrow->本登録者ＩＤ);
            $form['updated_at'] = $transrow->最終メンテ日;
            $form['updated_by'] = trim($transrow->最終メンテＩＤ);
            $findvalueset = $newtablename.'?code='.urlencode($form['code']);
            $id = $findvalueservice->findValue($findvalueset, 'id');
            $ret_id = $excuteprocessservice->excuteProcess($newtablename , $form, $id); 
        }
    }

    // メーカー発行のJANコードかどうか判断する
    private function judgeJancode($transrow) {
        // 新日本造形用のダミーコード
        if (substr($transrow->ＪＡＮコード, 0, 6) == '458222') {
            return '';  
        }
        // ワイズJANではない
        if (substr($transrow->ＪＡＮコード, 0, 7) != '4518917') {
            return $transrow->ＪＡＮコード;
        }
        // '4518917～が残った
        // 自社関連会社のオリジナル
        $groupcomp = [0, 4, 4510, 5700, 8990 ];
        if (in_array($transrow->仕入先コード, $groupcomp)) {
            return $transrow->ＪＡＮコード;
        }
        // カモ井のオリジナル
        if ($transrow->仕入先コード == 8590 && substr($transrow->商品名, 0, 1) == '※') {
            return $transrow->ＪＡＮコード;
        }
        // 当てはまらないのは元の商品にJANがない、わからないから付けただけ
        return '';
    }

    // 未登録の商品名をproductに追加する
    private function registProduct($product, $brand_id) {
        $getfuriganaservice = new GetFuriganaService;
        $transwnetservice = new TranswnetService;
        $form = [];
        $form['brand_id'] = $brand_id;
        $form['name'] = $product;
        $form['name_kana'] = $getfuriganaservice->GetFurigana($product);
        $form['url'] = '';
        $form['image'] = '';
        $form['remark'] = '';
        $form['updated_at'] = date("Y-m-d H:i:s");
        $form['updated_by'] = 'transwnet';
        $form += $transwnetservice->addCreatedToForm(NULL);
        $excuteprocessservice = new ExcuteProcessService;
        $id = 0;
        $product_id = $excuteprocessservice->excuteProcess('products' , $form, $id);
        return $product_id;
    }

    // 未登録のメーカー名をbrandに追加する
    private function registBrand($brand) {
        $getfuriganaservice = new GetFuriganaService;
        $transwnetservice = new TranswnetService;
        $form = [];
        $form['name'] = $brand;
        $form['name_kana'] = $getfuriganaservice->GetFurigana($brand);
        $form['url'] = '';
        $form['image'] = '';
        $form['remark'] = '';
        $form['updated_at'] = time();
        $form['updated_by'] = 'transwnet';
        $form += $transwnetservice->addCreatedToForm(NULL);
        $excuteprocessservice = new ExcuteProcessService;
        $id = 0;
        $brand_id = $excuteprocessservice->excuteProcess('brands' , $form, $id);
        return $brand_id;
    }
}
