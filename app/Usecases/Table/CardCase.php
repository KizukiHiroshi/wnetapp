<?php
declare(strict_types=1);
namespace App\Usecases\Table;

use App\Services\SessionService;
use App\Services\Database\GetForeginSelectsService;
use App\Services\Database\GetOptionSelectsService;
use App\Services\Table\ArangeColumnspropToCardService;
use App\Services\Table\GetEmptyRowService;
use App\Services\Table\GetMenuParamsService;
use App\Services\Table\GetRowByIdService;

class CardCase {

    public function __construct() {
    }
    
    public function getParams($request, $mode) {
        $tablename = $request->tablename;
        // 現在のテーブル名をSessionに保存する
        $sessionservice = new SessionService;
        $sessionservice->putSession('tablename', $tablename);
        // Table選択、検索表示のパラメータを取得する
        $getmenuparamsservice = new GetMenuParamsService;
        $params = $getmenuparamsservice->getMenuParams($request);
        // Card表示用のパラメータを取得する
        $params += $this->getCardParams($request, $mode);
        return $params;
    }

    // Card表示用のパラメータを取得する
    public function getCardParams($request, $mode) {
        $tablename = $request->tablename;
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $columnsprop = $sessionservice->getSession('columnsprop', $tablename);
        $id = $request->id;
        // 成功メッセージ
        $success = $request->success !== '' ? $request->success : '';
        // テーブルの和名
        $tablecomment = $modelindex[$tablename]['tablecomment'];
        // columnspropのforeign_referenceをcard表示用に合体する
        $arangecolumnsproptocardservice = new ArangeColumnspropToCardService;
        $cardcolumnsprop = $arangecolumnsproptocardservice->arangeColumnspropToCard($columnsprop);
        // foreignkey用のセレクトリストを用意する
        $getforeginselectsservice = new GetForeginSelectsService;
        $foreignselects = $getforeginselectsservice->getForeginSelects($columnsprop);
        // option用のセレクトリストを用意する
        $getoptionselectsservice = new GetOptionSelectsService;
        $optionselects = $getoptionselectsservice->getOptionSelects($columnsprop);
       
        $page = $sessionservice->getSession('page');

        if ($mode !== 'create') {
            // 新規作成以外では表示するレコードの実体を取得する
            $getrowbyidservice = new GetRowByIdService;
            $row = $getrowbyidservice->getRowById($request, $id);
        } else {
            // 空のレコードデータを作る
            $getemptyrowservice = new GetEmptyRowService;
            $row = $getemptyrowservice->getEmptyRow($cardcolumnsprop);
        }
        $params = [
            'mode'          => $mode,
            'tablename'     => $tablename,
            'success'       => $success,
            'tablecomment'  => $tablecomment,
            'cardcolumnsprop'   => $cardcolumnsprop,
            'foreignselects' => $foreignselects,
            'optionselects' => $optionselects,
            'row'           => $row,
            'page'          => $page,
        ];
        return $params;
    }
}

