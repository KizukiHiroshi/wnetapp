<?php
declare(strict_types=1);
namespace App\Usecases\Table;

use App\Services\SessionService;
use App\Services\Database\GetForeginSelectsService;
use App\Services\Table\GetEmptyRowService;
use App\Services\Table\GetRowByIdService;
use App\Services\Model\GetModelSelectParamsService;


class CardCase {

    public function __construct() {
    }
    
    public function getParams($request, $mode) {
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
        // columnspropをcard表示用に変更する
        $cardcolumnsprop = $sessionservice->getSession('cardcolumnsprop', $columnsprop);;
        if ($mode !== 'create') {
            // 新規作成以外では表示するレコードの実体を取得する
            $getrowbyidservice = new GetRowByIdService;
            $row = $getrowbyidservice->getRowById($request, $id);
        } else {
            // 空のレコードデータを作る
            $getemptyrowservice = new GetEmptyRowService;
            $row = $getemptyrowservice->getEmptyRow($cardcolumnsprop);
        }
        // foreignkey用のセレクトリストを用意する
        $searchinput = ['id' => $id];
        $getforeginselectsservice = new GetForeginSelectsService;
        $foreignselects = $getforeginselectsservice->getForeginSelects($cardcolumnsprop, $searchinput);
        $page = $sessionservice->getSession('page');
        $params = [
            'mode'          => $mode,
            'tablename'     => $tablename,
            'success'       => $success,
            'tablecomment'  => $tablecomment,
            'cardcolumnsprop'   => $cardcolumnsprop,
            'foreignselects' => $foreignselects,
            'row'           => $row,
            'page'          => $page,
        ];
        return $params;
    }

    // // $requestからtable_searchの情報を抽出してSessionに保存する
    // private function setIdToSearchinput($row) {
    //     $input =[];
    //     $rawparams = $row->toArray();
    //     foreach($rawparams as $rawname => $value) {
    //         if ($rawname == 'id') {
    //             $input[$rawname] = $value;
    //         }
    //     }
    //     return $input;
    // }

}
