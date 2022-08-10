<?php
declare(strict_types=1);
namespace App\Services\Table;

use App\Services\Database\ExcuteCsvprocessService;
use App\Services\Database\GetForeginSelectsService;
use App\Services\Database\GetOptionSelectsService;
use App\Services\SessionService;

class GetSearchUnitParamsService {

    public function __construct() {
    }
    
    // Menu表示用のパラメータを取得する
    public function getSearchUnitParams($request) {
        $tablename = $request->tablename;
        $sessionservice = new SessionService;
        // ■search用の変数
        // 表示する項目リスト
        $cardcolumnsprop = null;
        // 検索入力した履歴
        $searchconditions = [];
        // 検索入力のバリデーションエラー
        $searcherrors = null;
        // idによる参照用セレクト
        $foreignselects = null;
        // optによる参照用セレクト
        $optionselects = null;
        if ($tablename) {
            $columnsprop = $sessionservice->getSession('columnsprop', $tablename);
            $cardcolumnsprop = $sessionservice->getSession('cardcolumnsprop', $columnsprop);
            if ($this->has_SerachCondition($request)) { // 検索メニューからのリクエスト
                $searchconditions = $this->getSearchConditions($request);
                $searcherrors = $this->validateSearch($tablename, $columnsprop, $searchconditions);
            } else {    // リスト表示からのリクエスト
                $searchconditions = $sessionservice->getSession('searchconditions');
            }
            // 外部テーブル参照用のセレクト
            $getforeginselectsservice = new GetForeginSelectsService;
            $foreignselects = $getforeginselectsservice->getForeginSelects($cardcolumnsprop, $searchconditions);
            // オプション選択用のセレクト
            $getoptionselectsservice = new GetOptionSelectsService;
            $optionselects = $getoptionselectsservice->getOptionSelects($columnsprop);
        }
        $params = [
            'cardcolumnsprop'   => $cardcolumnsprop,
            'searchconditions'       => $searchconditions,
            'searcherrors'      => $searcherrors,
            'foreignselects'    => $foreignselects,
            'optionselects'     => $optionselects,
        ];
        return $params;
    }

    // $requestからtable_searchの情報を抽出してSessionに保存する
    private function getSearchConditions($request) {
        $searchconditions =[];
        $rawparams = $request->all();
        foreach($rawparams as $rawname => $value) {
            if (substr($rawname, 0, 7) == 'search_') {
                if (substr($rawname, -3) == '_id' && $value == "0") {
                    // _id = 0 はセレクタの無選択
                    $value = null;
                }
                $searchconditions[substr($rawname,7)] = $value;
            }
        }
        if (count($searchconditions) > 0) {
            $sessionservice = new SessionService;
            $sessionservice->putSession('searchconditions', $searchconditions);
        }
        return $searchconditions;
    }

    // $request中にtable_searchの情報を抽出してSessionに保存する
    private function has_SerachCondition($request) {
        $has_serachinput = false;
        $rawparams = $request->all();
        foreach($rawparams as $rawname => $value) {
            if (substr($rawname, 0, 7) == 'search_') {
                $has_serachinput = true;
                break;
            }
        }
        return $has_serachinput;
    }

    // 検索条件のValidation
    private function validateSearch($tablename, $columnsprop, $searchconditions) {
        $searcherrors  = [];
        // 通常検索分
        $searcherrors += $this->getSearcherros('', $tablename, $columnsprop, $searchconditions);
        // 開始条件検索分
        $searcherrors += $this->getSearcherros('bigin_', $tablename, $columnsprop, $searchconditions);
        // 終了条件検索分
        $searcherrors += $this->getSearcherros('end_', $tablename, $columnsprop, $searchconditions);
        if (count($searcherrors) > 0) {
            return $searcherrors ;       
        } else {
            return null;
        }
    }

    // searchValidationを開始値、終了値、通常毎に処理する
    private function getSearcherros($withhearder, $tablename, $columnsprop, $searchconditions) {
        $id = 0;
        $mode = "check";
        $getformforsearchservice = new GetFormforSearchService;
        $form = $getformforsearchservice->getFormforSearch($withhearder, $columnsprop, $searchconditions);
        $excutecsvprocessservice = new ExcuteCsvprocessService;
        $errortips = $excutecsvprocessservice->excuteCsvprocess($tablename, $form, $id, $mode);
        $searcherrors = $this->dropNonfitFromErrortips($withhearder, $errortips, $searchconditions);
        return $searcherrors;
    }

    // searchValidationからの戻り値から$oldinputの値が空のものを除く。
    // searchValidationからの戻り値から'はすでに使われています。''正しい形式の'を除く。
    private function dropNonfitFromErrortips($withhearder, $errortips, $searchconditions) {
        $errors = [];
        if ($errortips !== null) {
            $header = '';
            if ($withhearder == 'bigin_') {$header = '開始値:';}
            if ($withhearder == 'end_') {$header = '終了値:';}
            foreach ($errortips as $columnname => $error) {
                // $oldinputに値が入っているものだけをリストに追加する
                if (array_key_exists($withhearder.$columnname, $searchconditions) 
                    && $searchconditions[$withhearder.$columnname] !== null) {
                    $needederror = [];
                    foreach( $error as $errortext) {
                        // 'はすでに使われています。'が無いものだけをリストに追加する
                        if (strpos($errortext, 'はすでに使われています。') == false
                            && strpos($errortext, '正しい形式の') !== 0) {
                            $needederror[] = $errortext;
                        }
                    }
                    if (count($needederror) > 0) {
                        $errors[] = $header.implode( ',', array_values($needederror));
                    }
                }
            }
        }
        return $errors;      
    }
}

