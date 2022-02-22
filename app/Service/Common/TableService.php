<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Table表示に必要なデータの実体を取得する

declare(strict_types=1);
namespace App\Service\Common;

use App\Service\Common\DbioService;
use App\Service\Common\SortService;
use App\Service\Common\ModelService;
use App\Service\Common\SessionService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableService  {

    /* modelindex:全てのモデルの一覧
    tablename => [         // テーブルの物理名
        'modelname' => '',            // モデル名        
        'modelzone' => '',            // モデルの分類名
        'tablecomment' => '',         // テーブルの和名
    ] */

    /* columnsprop:表示用のカラム名とプロパティ
    columnname => [  // 表示カラム名
        'tablename' => '',  // 所属テーブルの物理名
        'sortcolumn' => '', // ソート時に使うカラム
        'type' => '',       // 変数タイプ
        'length' => '',     // 変数の長さ
        'comment' => '',    // カラムの和名
        'notnull' => '',    // NULL許可   
        'default' => '',    // 初期値
    ]*/
    private $dbioservice;
    private $sortservice;
    private $modelservice;
    private $sessionservice;
    public function __construct(
        DbioService $dbioservice, 
        SortService $sortservice, 
        ModelService $modelservice, 
        SessionService $sessionservice) {
        $this->dbioservice = $dbioservice;
        $this->sortservice = $sortservice;
        $this->modelservice = $modelservice;
        $this->sessionservice = $sessionservice;
    }
    
    // List表示用のパラメータを取得する
    public function getListParams($request) {
        $tablename = $request->tablename;
        $modelindex = $this->sessionservice->getSession('modelindex');
        $modelselects = $this->sessionservice->getSession('modelselects', $modelindex);
        $selectedtable = '';
        $tablecomment = '';
        $columnsprop = NULL;
        $rows = NULL;
        $lastsort = '';
        $success = '';
        $withbutton = NULL;
        $withdownload = NULL;
        $mode = '';
        $page = $request->page ? $request->page : $this->sessionservice->getSession('page');
        if (array_key_exists($tablename, $modelindex)) {
            // deviceのpropertyからpaginatecntを取得する
            $paginatecnt = 15;
            // 成功メッセージ
            $success = $request->success != '' ? $request->success : '';
            // 表示リストの詳細
            // テーブル名が更新されている時は既存の$columnspropを消す
            $lasttablename = $this->sessionservice->getSession('tablename');
            if ($lasttablename != $tablename) {$this->sessionservice->forgetSession('columnsprop');}
            $columnsprop = $this->sessionservice->getSession('columnsprop', $modelindex, $tablename);
            // モデル選択に渡す現在のテーブル名
            $selectedtable = $tablename;
            // テーブルの和名
            $tablecomment = $modelindex[$tablename]['tablecomment'];
            // 作業用に指定が必要な場合のソート順（ここでは既存テーブルの参照なので不要）
            $tasksort = null;
            // Listのソート順を取得する
            $tempsort = $this->sortservice->getTempsort($request, $modelindex, $columnsprop, $tasksort);
            // 表示するListの実体を取得する
            $rows = $this->dbioservice->getRows($request, $modelindex, $columnsprop, $tempsort, $paginatecnt);
            // 今回ソートの先頭部分を「行表示から戻ってきた時」のためにSessionに保存する
            $lastsort = ($tempsort ? array_key_first($tempsort).'--'.array_values($tempsort)[0] : '');
            $this->sessionservice->putSession('lastsort', $lastsort);
            // 現在のページを「行表示から戻ってきた時」のためにSessionに保存する
            $this->sessionservice->putSession('page', $page);
            // 現在のテーブル名をSessionに保存する
            $this->sessionservice->putSession('tablename', $tablename);
            // 行選択のボタンを表示する
            $withbutton = ['buttonvalue' => 'id', 'value' => '選択']; 
            // ダウンロードのボタンを表示する
            $withdownload = ['buttonvalue' => $tablename, 'value' => 'ダウンロード']; 
            // table.blade.phpでリストを表示するかどうかを判断するモード
            $mode = 'list';
        }
        $param = [
            'tablename'     => $tablename,
            'modelselects'  => $modelselects,
            'selectedtable' => $selectedtable,
            'tablecomment'  => $tablecomment,
            'columnsprop'   => $columnsprop,
            'rows'          => $rows,
            'success'       => $success,
            'withbutton'    => $withbutton,
            'withdownload'  => $withdownload,
            'mode'          => $mode,
        ];
        return $param;
    }

    // Card表示用のパラメータを取得する
    public function getCardParams($request, $mode){
        $tablename = $request->tablename;
        $modelindex = $this->sessionservice->getSession('modelindex');
        $modelselects = $this->sessionservice->getSession('modelselects', $modelindex);
        // テーブル名が更新されている時は既存の$columnspropを消す
        $lasttablename = $this->sessionservice->getSession('tablename');
        if ($lasttablename != $tablename) {$this->sessionservice->forgetSession('columnsprop');}
        $columnsprop = $this->sessionservice->getSession('columnsprop', $modelindex, $tablename);
        $id = $request->id;
        // 成功メッセージ
        $success = $request->success != '' ? $request->success : '';
        // テーブルの和名
        $tablecomment = $modelindex[$tablename]['tablecomment'];
        // モデル選択に渡す現在のテーブル名
        $selectedtable = $tablename;
        // columnspropのforeign_referenceをcard表示用に合体する
        $cardcolumnsprop = $this->modelservice->arangeColumnspropToCard($columnsprop);
        // foreignkey用のセレクトリストを用意する
        $foreignselects = $this->dbioservice->getForeginSelects($columnsprop);
        $page = $this->sessionservice->getSession('page');
        if ($mode!='create') {
            // 新規作成以外では表示するレコードの実体を取得する
            $row = $this->dbioservice->getRowById($request, $modelindex, $columnsprop, $id);
        } else {
            // 空のレコードデータを作る
            $row = $this->modelservice->getEmptyRow($cardcolumnsprop);
        }
        $params = [
            'mode'          => $mode,
            'modelselects'  => $modelselects,
            'selectedtable' => $selectedtable,
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

    // 表示Listのダウンロード用CSVを取得する   
    public function getDownloadCSV($request) {
        $modelindex = $this->sessionservice->getSession('modelindex');
        $tablename = $request->tablename;
        $columnsprop = $this->sessionservice->getSession('columnsprop', $modelindex, $tablename);
        // id,foreign_idを消す
        foreach ($columnsprop as $columnname => $pops) {
            if ($columnname == 'id' or substr($columnname, -3) == '_id') {
                unset($columnsprop[$columnname]);
            }
        }
        // table名部分
        $downloadcsv = [
            [$modelindex[$tablename]['tablecomment'],$tablename]
        ];
        // Property部分
        $downloadcsv[] = array_keys($columnsprop);
        $downloadcsv[] = array_column($columnsprop, 'type');
        $downloadcsv[] = array_column($columnsprop, 'notnull');
        $downloadcsv[] = array_column($columnsprop, 'comment');
        // List実体部分
        $downloadsql = $this->sessionservice->getSession('downloadsql');
        $rows = $this->dbioservice->getRowsByRawsql($downloadsql);
        foreach ($rows as $row) {
            $values = [];
            foreach ($columnsprop as $columnname => $pops) {
                array_push($values, $row->$columnname);
            }
            $downloadcsv[]= $values;
        }
        return $downloadcsv;
    }

}

