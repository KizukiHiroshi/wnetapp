<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Table表示に必要なデータの実体を取得する

declare(strict_types=1);
namespace App\Service\Common;

use App\Service\Common\DbioService;
use App\Service\Common\SortService;
use App\Service\Common\ModelService;
use App\Service\Common\SessionService;
use Illuminate\Support\Str;
use SplFileObject;

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
    
    // upload実行
    public function csvUpload($request, $mode) {
        // // ロケールを設定(日本語に設定)
        // setlocale(LC_ALL, 'ja_JP.UTF-8');
        // // アップロードしたファイルを取得
        // // 'csv_file' はビューの inputタグのname属性
        // $uploaded_file = $request->file('upload_file');
        //     // アップロードしたファイルの絶対パスを取得
        // $file_path = $request->file('upload_file')->path($uploaded_file);
        // //SplFileObjectを生成
        // $file = new SplFileObject($file_path);
        // //SplFileObject::READ_CSV が最速らしい
        // $file->setFlags(SplFileObject::READ_CSV);
        // $tablename = '';
        // $row_count = 1;
        // $error = NULL;
        // $rule = [];
        // $columnlist = [];
        // $rawform = [];
        // $form =[];
        // foreach ($file as $row) {
        //     // 最終行の処理(最終行が空っぽの場合の対策
        //     if ($row === [null]) continue; 
        //     if ($row_count == 1) {          // 1行目はテーブル名
        //         $tablename = $row[0];
        //         // validation rule 取得
        //         $rule = $this->getRule($tablename);
        //     } elseif ($row_count == 2) {    // 2行目はカラム名リスト
        //         // form決定
        //         $columnlist = $row;
        //     } else {                        // 3行目以後がデータ
        //         // uplaodしたままのデータを$rawform[]に入れる
        //         $colcnt = 0;
        //         foreach ($row AS $columnvalue) {
        //             // CSVの文字コードがSJISなのでUTF-8に変更
        //             $rawform[$columnlist[$colcnt]] = mb_convert_encoding($columnvalue, 'UTF-8', 'SJIS');
        //             $colcnt += 1;
        //         }
        //         // 他テーブル参照値からidを取得

        //         // validation
                
        //         // id検索
        //         // 1件ずつINSERT又はUPDATE
        //         //     Oldsql::insert(array(
        //         //         'sqltype' => $sqltype, 
        //         //         'sqltext' => $sqltext, 
        //         //         'is_checked' => $is_checked, 
        //         // ));
        //     }
        //     $row_count++;
        // }
        // $uploadresult = [
        //     'tablename' => $tablename,
        //     'row_count' => $row_count,
        //     'error'     => $error,
        // ];
        // return $uploadresult;
    }

    // private function getRule($tablename) {
    //     $modelindex = $this->sessionservice->getSession('modelindex');
    //     $modelfullname = $modelindex[$tablename]['modelname'];
    //     $modelpathname = Str::beforeLast($modelfullname, '\\');
    //     $modelname = Str::afterLast($modelfullname, '\\');
    //     $modeldirname = Str::afterLast($modelpathname, '\\');
    //     $targetrequest = 'App\Http\Requests\\'.$modeldirname.'\\'.$modelname.'Request';
    //     $myrequest = app()->make($targetrequest);
    //     $rule = $myrequest->rules();
    //     return $rule;    
    // }


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
            [$modelindex[$tablename]['tablecomment'], $tablename]
        ];
        // Property部分
        $downloadcsv[] = array_keys($columnsprop);  // columnname
        $downloadcsv[] = array_column($columnsprop, 'type');
        $downloadcsv[] = array_column($columnsprop, 'notnull');
        $downloadcsv[] = array_column($columnsprop, 'isunique');
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

    // Upload表示用のパラメータを取得する
    public function getUploadParams($request){
        $tablename = $request->tablename;
        $modelindex = $this->sessionservice->getSession('modelindex');
        $modelselects = $this->sessionservice->getSession('modelselects', $modelindex);
        $selectedtable = $tablename;
        $success = $request->success != '' ? $request->success : '';
        // テーブルの和名
        $tablecomment = $modelindex[$tablename]['tablecomment'];
        $params = [
            'mode'          => 'upload',
            'modelselects'  => $modelselects,
            'selectedtable' => $selectedtable,
            'tablename'     => $tablename,
            'success'       => $success,
            'tablecomment'  => $tablecomment,
            'tgtuploadfile' => $tablecomment.'_upload.csv',
        ];
        return $params;
    }
}

