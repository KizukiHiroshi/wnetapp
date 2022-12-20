<?php

namespace App\Jobs;

set_time_limit(0);

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Services\SequenceService;
use App\Services\Database\ExcuteProcessService;
use App\Services\Database\FindValueService;
use App\Services\Database\GetTransactionNoService;
use App\Services\Transwnet\TranswnetService;

class TransGetorderFromOrder implements ShouldQueue
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
        $systemname = 'TransGetorderFromOrder';
        $oldtablename = '１３：店舗発注明細';
        // while (true) {
            $maxvalue = $this->getKnownshopandcode($systemname);
            $transrows = $this->getTransRows($systemname, $oldtablename, $maxvalue);
            // $newtablenameを更新する
            $maxvalue = $this->updateNewTable($transrows, $maxvalue);
            // 管理済履歴を更新する
            $transwnetservice = new TranswnetService;
            $transwnetservice->updateTablereplacement($systemname, $oldtablename, $maxvalue);
            //  レコードが無ければexit
        //     if ($transrows->count() == 0) {
        //         break;
        //    }
        // }
    }

    // $newtablenameを更新する
    private function updateNewTable($transrows, $maxvalue) {
        $dictionaries = [
            'id' => [],
            'companykey' => [],
            'nowstring' => [],
        ];
        $templabelkey = $maxvalue;         // labelを更新するかどうか判断するkey
        $order_label_id = null;      // label
        $labelaccount = [];         // labelの集計値
        $labelaccount = $this->resetLabelAccount($labelaccount);
        $detail_no = 0;
        $taxrate = 10;
        foreach ($transrows as $transrow) {
            // 新テーブルに登録できるかチェック
            if (!$this->is_availablerow($transrow)) {
                continue;
            }
            // labelの更新確認
            $newlabelkey = str_replace('-', '', substr($transrow->店発注日,0,10)).'-'.trim(strval($transrow->店コード)).'-'.trim(strval($transrow->客注区分));
            if ($templabelkey !== $newlabelkey) {
                // labelに集計値を登録
                $this->updateLable($order_label_id, $labelaccount);
                // label集計値の初期化
                $labelaccount = $this->resetLabelAccount($labelaccount);
                $templabelkey = $newlabelkey;
                $detail_no = 0;
                // 新しいlabel発行
                $order_label_id = $this->addNewLabel($transrow, $dictionaries); 
            }
            // detailの登録
            $detail_no += 1;
            $detailacount = $this->addNewDetail($order_label_id, $detail_no, $taxrate, $transrow);
            // labelの集計値更新
            $labelaccount = $this->updateLableAccount($labelaccount, $detailacount);
        }
        // maxvalue更新
        $maxvalue = str_replace('-', '', substr($transrow->店発注日,0,10)).
        '-'.trim(strval($transrow->店コード)).
        '-'.trim(strval($transrow->客注区分)).
        '-'.trim(strval($transrow->発生ＩＤ));
        return $maxvalue;
    }

    // 新テーブルに登録できるかチェック
    private function is_availablerow($transrow) {
        $findvalueservice = new FindValueService;
        $transwnetservice = new TranswnetService;
        // 発注元
        $shopcode = $transrow->店コード;
        $separatedshopcode = $transwnetservice->separateRawShopcode($shopcode);
        $foreginkey = 'companies?code='.urlencode($separatedshopcode['companycode']);
        $company_id = $findvalueservice->findValue($foreginkey);
        if ($company_id == 0) {
            return false;
        };
        $foreginkey = 'businessunits?company_id='.$company_id.'&&code='.urlencode($separatedshopcode['businessunitcode']);
        $businessunit_id = $findvalueservice->findValue($foreginkey);
        if ($businessunit_id == 0) {
            return false;
        };
        // 発注先
        $vendorcode = trim($transrow->仕入先コード);
        $foreginkey = 'companies?code='.urlencode($vendorcode);
        $vendorcompany_id = $findvalueservice->findValue($foreginkey);
        if ($vendorcompany_id == 0) {
            return false;
        };
        // 商品
        $productitenmcode = trim($transrow->ＪＡＮコード);
        $foreginkey = 'productitems?code='.urlencode($productitenmcode);
        $productitem_id = $findvalueservice->findValue($foreginkey);
        if ($productitem_id == 0) {
            return false;
        };
        return true;
    }

    // labelに集計値を登録
    private function updateLable($order_label_id, $labelaccount) {
        if (!$order_label_id) {
            return;
        }
        $form = [];
        // detail_count
        $form['detail_count'] = $labelaccount['detail_count'];
        // regularprice_total
        $form['regularprice_total'] = $labelaccount['regularprice_total'];
        // price_total
        $form['price_total'] = intval($labelaccount['price_total']);
        // tax_total
        $form['tax_total'] = intval($labelaccount['tax_total']);
        $excuteprocessservice = new ExcuteProcessService;
        $id = $order_label_id;
        $ret_id = $excuteprocessservice->excuteProcess('order_labels' , $form, $id); 
    }

    // 新しいlabel発行
    private function addNewLabel($transrow, $dictionaries) {
        $excuteprocessservice = new ExcuteProcessService;
        $findvalueservice = new FindValueService;
        $sequenceservice = new SequenceService;
        $transwnetservice = new TranswnetService;
        $form = [];
        // 発注元のid
        $shopcode = $transrow->店コード;
        $separatedshopcode = $transwnetservice->separateRawShopcode($shopcode);
        if ($separatedshopcode['companycode'] == null || $separatedshopcode['businessunitcode'] == null ) {
            $find = 0;
        }
        $dictionaries['id'] = $transwnetservice->addIdBySeparatedShopcode($separatedshopcode, $dictionaries['id']);
        // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $foreginkey = 'companies?code='.urlencode($separatedshopcode['companycode']);
        $company_id = $dictionaries['id'][$foreginkey];
        $foreginkey = 'businessunits?company_id='.$company_id.'&&code='.urlencode($separatedshopcode['businessunitcode']);
        $businessunit_id = $dictionaries['id'][$foreginkey];
        // order_on
        $form['order_on'] = $transrow->店発注日;
        $rawyear = date('Y', strtotime($transrow->店発注日));
        $foreginkey = 'performer_in_companies?company_id='.$company_id;
        $dictionaries['companykey'] = $this->addSomedictionary($dictionaries['companykey'], 'sequence_key', $foreginkey);
        $companykey = $dictionaries['companykey'][$foreginkey];
        $key = 'order_'.$companykey;
        $foreginkey = 'sequences?key='.$key;
        $dictionaries['nowstring'] = $this->addSomedictionary($dictionaries['nowstring'], 'nowstring', $foreginkey);
        $nowstring = $dictionaries['nowstring'][$foreginkey];
        if ($rawyear > $nowstring) {
            // sequecesのnowstringを更新する
            $this->updateNowstring($key, $rawyear);
        }
        // order__company_id
        $form['order__company_id'] = $company_id;
        // order__businessunit_id
        $form['order__businessunit_id'] = $businessunit_id;
        // ordered_by
        $biko = trim($transrow->備考);
        $ordered_by = trim(strpos($biko, ' ') ? substr($biko, 0, strpos($biko, ' ')) : $biko);
        $ordered_by = mb_substr($ordered_by, 0, 6);
        $form['ordered_by'] = $ordered_by;
        // 店コードによって発注先を取得
        $getorderseparatedshopcode = $this->getGetorderSeparatedShopcode($transrow, $dictionaries);
        if ($getorderseparatedshopcode == null ) {
            $find = 0;
        }
        $dictionaries['id'] = $transwnetservice->addIdBySeparatedShopcode($getorderseparatedshopcode, $dictionaries['id']);
        // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $foreginkey = 'companies?code='.urlencode($getorderseparatedshopcode['companycode']);
        $getorder__company_id = $dictionaries['id'][$foreginkey];
        $foreginkey = 'businessunits?company_id='.$getorder__company_id.'&&code='.urlencode($getorderseparatedshopcode['businessunitcode']);
        $getorder__businessunit_id = $dictionaries['id'][$foreginkey];
        // getorder__company_id
        $form['getorder__company_id'] = $getorder__company_id;
        // getorder__businessunit_id
        $form['getorder__businessunit_id'] = $getorder__businessunit_id;
        // need_deliverydate
        $form['need_deliverydate'] = ($transrow->客注区分 == null ? 0 : 1);
        // due_date
        // detail_count
        $form['detail_count'] = 0;
        // regularprice_total
        $form['regularprice_total'] = 0;
        // price_total
        $form['price_total'] = 0;
        // tax_total
        $form['tax_total'] = 0;
        // delivery__businessunit_id
        $form['delivery__businessunit_id'] = $businessunit_id;
        // is_recieved
        $form['is_recieved'] = 0;
        // published_on
        $form['published_on'] = $transrow->店発注日;       
        // remark
        $form['remark'] = $transrow->備考;
        // is_completed
        $form['is_completed'] = 0;
        // old13id
        $form['old13id'] = $transrow->発生ＩＤ;
        // old14id
        $form['created_by'] = $ordered_by;
        $form['updated_by'] = $ordered_by;
        // order_no
        $gettransactionnoservice = new GetTransactionNoService;
        $form['order_no'] = $gettransactionnoservice->getTransactionNo($key, $rawyear);
        // transaction_no
        $form['alltransaction_no'] = $sequenceservice->getNewNo('alltransaction_no');
        $id = 0;
        $order_label_id = $excuteprocessservice->excuteProcess('order_labels' , $form, $id);
        return $order_label_id;
    }

    // detailの登録
    private function addNewDetail($order_label_id, $detail_no, $taxrate, $transrow) {
        $excuteprocessservice = new ExcuteProcessService;
        $findvalueservice = new FindValueService;
        $sequenceservice = new SequenceService;
        $form = [];
        // 発注状態が9は削除
        // order_label_id
        $form['order_label_id'] = $order_label_id;
        // detail_no
        $form['detail_no'] = $detail_no;
        // productitem_id
        $foreginkey = 'productitems?code='.$transrow->ＪＡＮコード;
        $productitem_id = $findvalueservice->findValue($foreginkey, 'id');
        $form['productitem_id'] = $productitem_id;
        // regularprice
        $form['regularprice'] = $transrow->販売単価;
        // price
        $form['price'] = $transrow->卸売単価;
        // quantity
        $form['quantity'] = $transrow->決定発注数;
        // taxrate
        $form['taxrate'] = $taxrate;
        // is_fixed
        $form['is_fixed'] = 0;
        // remark
        $form['remark'] = $transrow->備考;
        // available_quantity
        $form['available_quantity'] = 0;
        // is_completed
        $form['is_completed'] = 0;
        // old13
        $form['old13id'] = $transrow->発生ＩＤ;
        // old14
        $biko = trim($transrow->備考);
        $ordered_by = strpos($biko, ' ') ? substr($biko, 0, strpos($biko, ' ')) : $biko;
        $ordered_by = mb_substr($ordered_by, 0, 6);
        $form['created_by'] = $ordered_by;
        $form['updated_by'] = $ordered_by;
        //      
        $foreginkey = 'order_details?old13id='.$form['old13id'];
        $id = $findvalueservice->findValue($foreginkey, 'id');
        if ($id == 0) {
            // transaction_no
            $form['alltransaction_no'] = $sequenceservice->getNewNo('alltransaction_no');
        }
        $ret_id = $excuteprocessservice->excuteProcess('order_details' , $form, $id); 
        $detailacount = [
            'regularprice'    => $transrow->販売単価 * $transrow->決定発注数,
            'price'           => $transrow->販売単価 * $transrow->決定発注数,
            'tax'             => $transrow->販売単価 * $transrow->決定発注数 * $taxrate * 0.01,
        ];
        return  $detailacount;
    }

    // labelの集計値更新
    private function updateLableAccount($labelaccount, $detailacount) {
        $labelaccount['regularprice_total'] += $detailacount['regularprice'];
        $labelaccount['price_total'] += $detailacount['price'];
        $labelaccount['tax_total'] += $detailacount['tax'];
        $labelaccount['detail_count'] += 1;
        return $labelaccount;
    }

    // 店コードによって発注先を取得
    private function getGetorderSeparatedShopcode($transrow, $dictionaries) {
        $transwnetservice = new TranswnetService;
        $shopcode = $transrow->店コード;
        $getseparatedshopcode = [
            'companycode' => null,
            'businessunitcode' => null,
        ];
        if (intval($shopcode / 100000) == 0) {
            if ($shopcode % 100000 !== 99999) {
                $getseparatedshopcode = [
                    'companycode' => '0001',
                    'businessunitcode' => '99999',
                ];
            } else {
                return null;
            }
        } elseif (intval($shopcode / 100000) == 2 || intval($shopcode / 100000) == 3) {
            $getseparatedshopcode = [
                'companycode' => '0001',
                'businessunitcode' => '00002',
            ];
        } elseif (intval($shopcode / 100000) == 30 || intval($shopcode / 100000) == 2620) {
            if ($transrow->主体コード == 0) {
                $getseparatedshopcode = [
                    'companycode' => '0001',
                    'businessunitcode' => '00041',
                ];
            } else {
                $getseparatedshopcode = [
                    'companycode' => '5700',
                    'businessunitcode' => '99999',
                ];              
            }
        } else {
            $getseparatedshopcode = [
                'companycode' => '5700',
                'businessunitcode' => '99999',
            ];              
        }
        return $getseparatedshopcode;
    }

    // label集計値の初期化
    private function resetLabelAccount($labelaccount) {
        $labelaccount = [
            'regularprice_total'    => 0,
            'price_total'           => 0,
            'tax_total'             => 0,
            'detail_count'          => 0,
        ];
        return $labelaccount;
    }

    private function getTransRows($systemname, $oldtablename, $maxvalue) {
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
            ->whereRaw("(convert(int, 店コード/100000) <= 9 
            or convert(int, 店コード/100000) = 30 
            or convert(int, 店コード/100000) = 262 
            or convert(int, 店コード/100000) = 5700 
            or convert(int, 店コード/100000) = 8990 
            or convert(int, 店コード/100000) = 9200 
            or convert(int, 店コード/100000) = 9500 
            or convert(int, 店コード/100000) = 9505) AND (店コード % 100000 <> 99999)")
            ->whereRaw("(convert(VARCHAR, 店発注日, 112)+'-'+RTRIM(convert(CHAR, 店コード))
            +'-'+RTRIM(convert(CHAR, ISNULL(客注区分,0)))+'-'+RTRIM(convert(CHAR, 発生ＩＤ)) > '".$maxvalue."')")
            ->orderByRaw("convert(VARCHAR, 店発注日, 112)
            +'-'+RTRIM(convert(CHAR, 店コード))
            +'-'+RTRIM(convert(CHAR, ISNULL(客注区分, 0)))
            +'-'+RTRIM(convert(CHAR, 発生ＩＤ))")
            ->limit(3000)
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

    private function addSomedictionary($somedictionary, $tgtcolumn, $foreginkey) {
        if (array_key_exists($foreginkey, $somedictionary)) {
            // 登録済なら何もしない
        } else {
            // 未登録の参照を$iddictionaryに追加する
            $findvalueservice = new FindValueService;
            $tgtvalue = $findvalueservice->findValue($foreginkey, $tgtcolumn);
            if ($tgtvalue === 0) {
                $tgtvalue = NULL;
            }
            $somedictionary[$foreginkey] = $tgtvalue;
        }
        return $somedictionary;
    }

    // sequecesのnowstringを更新する
    private function updateNowstring($key, $rawyear) {
        $excuteprocessservice = new ExcuteProcessService;
        $findvalueservice = new FindValueService;
        $foreginkey = 'sequences?key='.$key;
        $id = $findvalueservice->findValue($foreginkey, 'id');
        $form['nowstring'] = $rawyear;
        DB::table('sequences')->where('key', $key)
        ->update(['nowstring' => $rawyear], ['sequence' => 0]);   
    }
}
