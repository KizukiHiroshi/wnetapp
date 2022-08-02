<?php
declare(strict_types=1);
namespace App\Usecases\Table;

use App\Services\SessionService;
use App\Services\Table\GetTableRowsService;
use App\Services\Table\GetSearchParamsService;
use App\Services\Table\SortService;
use App\Services\Model\GetModelSelectParamsService;

class ListCase {

    private $sessionservice;
    public function __construct() {
    }
    
    public function getParams($request) {
        $sessionservice = new SessionService;
        // 現在のテーブル名をSessionに保存する
        $tablename = $request->tablename;
        $sessionservice->putSession('tablename', $tablename);
        // 使用中のデバイス名を取得する
        $devicename = $sessionservice->getSession('devicename');
        // ModelIndexを取得する
        $modelindex = $sessionservice->getSession('modelindex');
        // パラメータを取得する
        $params = [
            'devicename'        => $devicename,
        ];
        // モデル選択部
        $getmodelselectparamsservice = new GetModelSelectParamsService;
        $params += $getmodelselectparamsservice->getModelselectParams($request, $modelindex);
        // テーブル検索条件記入部
        $getsearchparamsservice = new GetSearchParamsService;
        $params += $getsearchparamsservice->getSearchParams($request);
        // リスト表示部
        $params += $this->getListParams($request, $params);
        return $params;
    }

    // List表示用のパラメータを取得する
    private function getListParams($request, $params = null) {
        $sessionservice = new SessionService;
        $tablename = $request->tablename;
        $modelindex = $sessionservice->getSession('modelindex');
        $tablecomment = '';
        $columnsprop = NULL;
        $rows = NULL;
        $lastsort = '';
        $success = '';
        $withbutton = NULL;
        $withdownload = NULL;
        $mode = '';
        $page = $request->page ? $request->page : $sessionservice->getSession('page');
        $searchinput = $params['searchinput'];
        if (array_key_exists($tablename, $modelindex)) {
            // リストの画面当たり行数
            $paginatecnt = $sessionservice->getSession('paginatecnt');
            if (!$paginatecnt) {$paginatecnt = 18;}
            // 成功メッセージ
            $success = $request->success !== '' ? $request->success : '';
            // 表示リストの詳細
            // 表示用のカラム名とプロパティ
            $columnsprop = $sessionservice->getSession('columnsprop', $tablename);
            // テーブルの和名
            $tablecomment = $modelindex[$tablename]['tablecomment'];
            // 作業用に指定が必要な場合のソート順（ここでは既存テーブルの参照なので不要）
            $tasksort = null;
            // Listのソート順を取得する
            $sortservice = new SortService;
            $tempsort = $sortservice->getTempsort($request, $modelindex, $columnsprop, $tasksort);
            // 表示するListの実体を取得する
            $is_pagerequest = $this->getIspagerequest($request);
            if ($is_pagerequest) {
                $searchinput = $sessionservice->getSession('searchinput');
                $searchinput = is_null($searchinput) ? [] : $searchinput;
            }
            $gettablerowsservice = new GetTableRowsService;
            $rows = $gettablerowsservice->getTableRows($request, $tempsort);
            // 今回ソートの先頭部分を「行表示から戻ってきた時」のためにSessionに保存する
            $lastsort = ($tempsort ? array_key_first($tempsort).'--'.array_values($tempsort)[0] : '');
            $sessionservice->putSession('lastsort', $lastsort);
            // 現在のページを「行表示から戻ってきた時」のためにSessionに保存する
            $sessionservice->putSession('page', $page);
            // 行選択のボタンを表示する
            $withbutton = ['buttonvalue' => 'id', 'value' => '選択']; 
            // ダウンロードのボタンを表示する
            $withdownload = ['buttonvalue' => $tablename, 'value' => 'ダウンロード']; 
            // table.blade.phpでリストを表示するかどうかを判断するモード
            $mode = 'list';
        }
        $params = [
            'tablename'     => $tablename,
            'tablecomment'  => $tablecomment,
            'columnsprop'   => $columnsprop,
            'rows'          => $rows,
            'success'       => $success,
            'withbutton'    => $withbutton,
            'withdownload'  => $withdownload,
            'mode'          => $mode,
            'searchinput'   => $searchinput,    // 元の$paramsを上書きする
        ];
        return $params;
    }

    // pagenateのリクエストかどうか判断する
    private function getIspagerequest($request) {
        $is_pagerequest = false;
        $requestparams = $request->all();
        if (count($requestparams ) == 2 
            && array_key_exists('tablename', $requestparams )
            && array_key_exists('page', $requestparams )) {
            $is_pagerequest = true;
        }
        return $is_pagerequest;
    }
}

