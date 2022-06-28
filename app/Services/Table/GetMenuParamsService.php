<?php
declare(strict_types=1);
namespace App\Services\Table;

use App\Services\Database\ExcuteCsvprocessService;
use App\Services\Database\GetForeginSelectsService;
use App\Services\SessionService;
use App\Services\Table\GetFormforSearchService;

class GetMenuParamsService {

    public function __construct() {
    }
    
    // Menu表示用のパラメータを取得する
    public function getMenuParams($request) {
        $tablename = $request->tablename;
        // モデル選択に渡す現在のテーブル名
        $selectedtable = $tablename;
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $modelselect = $this->getModelselect($modelindex);
        $devicename = $sessionservice->getSession('devicename');;
        // search用の変数
        $cardcolumnsprop = null;
        $searchinput = [];
        $searcherrors = null;
        $foreignselects = null;
        if ($tablename) {
            $columnsprop = $sessionservice->getSession('columnsprop', $tablename);
            $arangecolumnsproptocardservice = new ArangeColumnspropToCardService;
            $cardcolumnsprop = $arangecolumnsproptocardservice->arangeColumnspropToCard($columnsprop);
            $searchinput = $this->setSerachinput($request);
            $searcherrors  =$this->validateSerch($tablename, $columnsprop, $searchinput);
            $getforeginselectsservice = new GetForeginSelectsService;
            $foreignselects = $getforeginselectsservice->getForeginSelects($columnsprop);    
        }
        $params = [
            'devicename'        => $devicename,
            'selectedtable'     => $selectedtable,
            'modelselect'       => $modelselect,
            'cardcolumnsprop'   => $cardcolumnsprop,
            'searchinput'       => $searchinput,
            'searcherrors'      => $searcherrors,
            'foreignselects'    => $foreignselects,
        ];
        return $params;
    }

    /* getModelselect:モデル選択用のmodelzone,tablecommentのグループセレクト配列
    tablename => [         // テーブルの物理名
        'group'     => modelzone        // テーブルの属するゾーン
        'value'     => tablecomment,    // テーブル和名        
    ] 
    */
    private function getModelselect($modelindex) {
        $modelselect = []; // 返すグループセレクト配列名
        foreach($modelindex as $key => $model) {
            $modelselect[$key] = [
                'group' => $model['modelzone'],
                'value' => $model['tablecomment'],
            ];
        }
        return $modelselect;
    }

    // $requestからtable_searchの情報を抽出してSessionに保存する
    private function setSerachinput($request) {
        $searchinput =[];
        $rawparams = $request->all();
        foreach($rawparams as $rawname => $value) {
            if (substr($rawname,0,7) == 'search_') {
                $searchinput[substr($rawname,7)] = $value;
            }
        }
        if (count($searchinput) > 0) {
            $sessionservice = new SessionService;
            $sessionservice->putSession('searchinput', $searchinput);
        }
        return $searchinput;
    }

    // 検索条件のValidation
    private function validateSerch($tablename, $columnsprop, $searchinput) {
        $searcherrors  = [];
        // 通常検索分
        $searcherrors += $this->getSearcherros('', $tablename, $columnsprop, $searchinput);
        // 開始条件検索分
        $searcherrors += $this->getSearcherros('bigin_', $tablename, $columnsprop, $searchinput);
        // 終了条件検索分
        $searcherrors += $this->getSearcherros('end_', $tablename, $columnsprop, $searchinput);
        if (count($searcherrors) > 0) {
            return $searcherrors ;       
        } else {
            return null;
        }
    }

    // searchValidationを開始値、終了値、通常毎に処理する
    private function getSearcherros($withhearder, $tablename, $columnsprop, $searchinput) {
        $id = 0;
        $mode = "check";
        $getformforsearchservice = new GetFormforSearchService;
        $form = $getformforsearchservice->getFormforSearch($withhearder, $columnsprop, $searchinput);
        $excutecsvprocessservice = new ExcuteCsvprocessService;
        $errortips = $excutecsvprocessservice->excuteCsvprocess($tablename, $form, $id, $mode);
        $searcherrors = $this->dropNonfitFromErrortips($withhearder, $errortips, $searchinput);
        return $searcherrors;
    }

    // searchValidationからの戻り値から$oldinputの値が空のものを除く。
    // searchValidationからの戻り値から'はすでに使われています。''正しい形式の'を除く。
    private function dropNonfitFromErrortips($withhearder, $errortips, $searchinput) {
        $errors = [];
        if ($errortips !== null) {
            $header = '';
            if ($withhearder == 'bigin_') {$header = '開始値:';}
            if ($withhearder == 'end_') {$header = '終了値:';}
            foreach ($errortips as $columnname => $error) {
                // $oldinputに値が入っているものだけをリストに追加する
                if (array_key_exists($withhearder.$columnname, $searchinput) 
                    && $searchinput[$withhearder.$columnname] !== null) {
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

