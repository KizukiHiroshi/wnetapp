<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Table表示に必要なデータの実体を取得する

declare(strict_types=1);
namespace App\Service\Common;

use App\Service\Common\CommonService;
use App\Service\Common\DbioService;
use App\Service\Common\SortService;
use App\Service\Common\ModelService;
use App\Service\Common\SessionService;
use Illuminate\Support\Facades\Auth;
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
    private $commonservice;
    private $dbioservice;
    private $sortservice;
    private $modelservice;
    private $sessionservice;
    public function __construct(
        CommonService $commonservice, 
        DbioService $dbioservice, 
        SortService $sortservice, 
        ModelService $modelservice, 
        SessionService $sessionservice) {
            $this->commonservice = $commonservice;
            $this->dbioservice = $dbioservice;
            $this->sortservice = $sortservice;
            $this->modelservice = $modelservice;
            $this->sessionservice = $sessionservice;
    }
    
    // Upload表示用のパラメータを取得する
    public function getUploadParams($request, $uploadresult=[]){
        $csvmode = $request->csvmode;
        $tablename = $request->tablename;
        $success = $request->success !== '' ? $request->success : '';
        $success = array_key_exists('success', $uploadresult) ? $uploadresult['success'] : $success ;
        $csvmode = array_key_exists('csvmode', $uploadresult) ? $uploadresult['csvmode'] : $csvmode;
        $errormsg = array_key_exists('errormsg', $uploadresult) ? $uploadresult['errormsg'] : '' ;
        $csverrors = array_key_exists('csverrors', $uploadresult) ? $uploadresult['csverrors'] : [] ;
        // テーブルの和名
        $modelindex = $this->sessionservice->getSession('modelindex');
        $tablecomment = $modelindex[$tablename]['tablecomment'];
        $params = [
            'mode'          => $csvmode,
            'tablename'     => $tablename,
            'tablecomment'  => $tablecomment,
            'tgtuploadfile' => $tablecomment.'_upload.csv',
            'success'       => $success,
            'errormsg'      => $errormsg,
            'csverrors'     => $csverrors,
        ];
        return $params;
    }

    // upload実行
    public function csvUpload($request) {
        $csvmode = $request->csvmode;
        $uploadresult = [
            'errormsg' => '',
            'csvmode'  => $csvmode,
        ];
        if ($csvmode == 'csvselect') {
            return $uploadresult;
        } else {
            // ロケールを設定(日本語に設定)
            setlocale(LC_ALL, 'ja_JP.UTF-8');
            // アップロードしたファイルを取得
            // 'upload_file' はビューの inputタグのname属性
            $tablename = $request->tablename;
            $userid = Auth::id();
            $savedfilename = $tablename.'_upload_'.strval($userid).'.csv';
            $uploadresult['savedfilename'] = $savedfilename;
            $uploadway = $request->uploadway;
            if ($csvmode == 'csvcheck') {
                $uploaded_file = $request->file('upload_file');
                if ($uploaded_file !== null) {
                    $uploaded_file->storeAs('public/csv/', $savedfilename);
                }
            }
            if(!file_exists(storage_path('app/public/csv/'.$savedfilename ))){
                $uploadresult['errormsg'] = 'ファイルが選択されていません';
                return $uploadresult;        
            }
            // アップロードしたファイルの絶対パスを取得
            $file_path = storage_path().'\\app\\public\\csv\\'.$savedfilename;
            //SplFileObjectを生成
            $file = new SplFileObject($file_path);
            //SplFileObject::READ_CSV が最速らしい
            $file->setFlags(SplFileObject::READ_CSV);
            $row_count = 1;     // uploadfile用カウンター
            $csverrors = [];        // errorメッセージ
            $rawcolumns = [];   // uploadファイルのカラムリスト
            $iddictionary = $this->sessionservice->getSession('iddictionary');   // テーブル参照idリスト
            $rawform = [];      // uploadされたままの値リスト
            $form =[];          // 更新用に加工済の値リスト
            foreach ($file as $row) {
                // 最終行の処理(最終行が空っぽの場合の対策
                if ($row == [null]) continue; 
                if ($row_count == 1) {          // 1行目はテーブル名
                    if ($row[0]!=$tablename) {
                        $uploadresult['errormsg'] = 'ファイルの内容が不正です';
                        return $uploadresult;
                    }
                } elseif ($row_count == 2) {    // 2行目はカラム名リスト
                    // uploadファイルのカラムリスト
                    $rawcolumns = $row;
                    // $rawcolumns内の参照キー
                } else {                        // 3行目以後がデータ
                    // uplaodされたままの値を$rawformに入れる
                    $colcnt = 0;
                    foreach ($row AS $columnvalue) {
                        // CSVの文字コードがSJISなのでUTF-8に変更
                        $rawform[$rawcolumns[$colcnt]] = mb_convert_encoding($columnvalue, 'UTF-8', 'SJIS');
                        $colcnt += 1;
                    }
                    // テーブル参照情報取得
                    $foreginkeys = $this->getForeginkeys($rawform);
                    if ($foreginkeys) {
                        $foreginid = null;
                        // $iddictionary内にあるか確認
                        foreach ($foreginkeys as $foreginkey) {
                            // テーブル参照値から参照テーブルidを取得
                            if ($iddictionary) {
                                if (array_key_exists($foreginkey, $iddictionary)) {
                                    $foreginid = $iddictionary[$foreginkey];
                                } else {
                                    $foreginid = $this->dbioservice->findId($foreginkey);
                                    $iddictionary[$foreginkey] = $foreginid;
                                }
                            } else {                   
                                $foreginid = $this->dbioservice->findId($foreginkey);
                                $iddictionary[$foreginkey] = $foreginid;
                            }
                            if (!$foreginid) {
                                // 必要なアラート
                                $csverrors[] = strval($row_count-2).':'.$foreginkey.' は未登録の参照です';
                                // 参照元更新が許可されていれば処理
                            }
                        }
                    }           
                    // テーブルに登録できる値リストに更新
                    $form = $this->modelservice->arangeForm($tablename, $rawform, $foreginkeys, $iddictionary);
                    // validation
                    if ($uploadway == 'allstore') {
                        // 全て新規なので$idはnull
                        $id = null;
                    } else {
                        // $formの$idを探しに行く
                        $findkey = $this->getFindkey($tablename, $form);
                        $id = $this->dbioservice->findId($findkey);
                        if ($id == 'many') {
                            $csverrors[] = strval($row_count-2).':'.$findkey.' は複数の行を変更します';
                        }
                    }
                    if ($csvmode == 'csvcheck') {
                        $mode = 'check';
                        $errortips = $this->dbioservice->excuteCsvprocess($tablename, $form, $id, $mode);
                        if ($errortips != null) {
                            $errortip = '';
                            foreach ($errortips as $key => $errors) {
                                $errortip .= implode( ',', array_values($errors));
                            }
                            $csverrors[] = strval($row_count-2).':'.$errortip;
                        }
                    } elseif ($csvmode == 'csvsave') {
                        // _byの値を入れる
                        $mode = !$id ? 'store' : 'update';
                        $form = $this->modelservice->addBytoForm($rawcolumns, $form, $mode);
                        // 実行
                        $mode = 'save';
                        $errortips = $this->dbioservice->excuteCsvprocess($tablename, $form, $id, $mode);
                        if ($errortips != null && $errortips != true) {
                            $errortip = '';
                            foreach ($errortips as $key => $errors) {
                                $errortip .= implode( ',', array_values($errors));
                            }
                            $csverrors[] = strval($row_count-2).':'.$errortip;
                            $uploadresult += [
                                'tablename' => $tablename,
                                'row_count' => $row_count,
                            ];
                            return $uploadresult;                                            
                        }
                    }
                }
                $row_count++;
            }            
        }
        $treatedcount = $row_count-3;
        if ($csvmode == 'csvcheck') {
            $this->sessionservice->putSession('iddictionary', $iddictionary);    // テーブル参照id辞書
            $uploadresult['csverrors'] = $csverrors;
            if (count($csverrors) == 0) {
                $uploadresult['success'] = $treatedcount.'件 エラーはありませんでした';
                $uploadresult['csvmode'] = 'csvsave';
            }
        } elseif ($csvmode == 'csvsave') {
            if (count($csverrors) == 0) {
                $uploadresult['success'] = $treatedcount.'件 登録しました';
            }
            $this->sessionservice->forgetSession('iddictionary');   // テーブル参照id辞書
            // strage/app/public/csv内の自分のファイル削除
            $this->killMyfile($userid);
        }
        $uploadresult += [
            'tablename' => $tablename,
            'treatedcount' => $treatedcount,
        ];
        return $uploadresult;
    }

    // $formのデータで$idを取得するためのkey作成
    // 参照テーブル名?参照カラム名=値&参照カラム名=値
    private function getFindkey($tablename, $form) {
        // テーブルのユニークキーを取得
        $findkey = null;
        $modelindex = $this->sessionservice->getSession('modelindex');
        $uniquekeys = $modelindex[$tablename]['modelname']::$uniquekeys;
        if (count($uniquekeys) > 0) {
            $separator = count($uniquekeys) == 1 ? '&&' : '&';
            $cnt = 1;
            foreach ($uniquekeys as $key => $uniquekey) {
                if ($cnt == 1) {
                    $findkey = $tablename.'?';
                } else {
                    $findkey .= $separator;
                }
                foreach ($uniquekey as $colname){
                    if (array_key_exists($colname, $form)) {
                        $findkey .= $colname.'='.$form[$colname];
                    }
                }
                $cnt +=1;
            }
        }    
        return $findkey;
    }

    // $foreginkeys = [参照テーブル名?参照カラム名=値&参照カラム名=値,]
    private function getForeginkeys($rawform) {
        $foreginkeys =[];
        $findidset = $this->getForeginFindidset($rawform);
        foreach ($findidset as $foregintablename => $colandvalue) {
            $foreginkey = $this->getFindkey($foregintablename, $colandvalue);
            $foreginkeys[] = $foreginkey;
        }
        return $foreginkeys;
    }

    // $findidset = [参照テーブル名 => [参照カラム名 => 値, 参照カラム名 => 値,],]
    private function getForeginFindidset($rawform) {
        $findidset = [];
        foreach($rawform as $colname => $value) {
            if (Str::contains($colname,'_id_')) {
                $foregintablename = Str::plural(Str::before($colname,'_id_'));
                $foregincolname = Str::after($colname,'_id_');
                if (array_key_exists($foregintablename, $findidset)) {
                    $findidset[$foregintablename] = 
                        array_merge($findidset[$foregintablename],[ $foregincolname => $value ]);
                } else {
                    $findidset[$foregintablename] = [ $foregincolname => $value ];
                }        
            }
        }
        return $findidset;
    }

    // Menu表示用のパラメータを取得する
    public function getMenuParams($request) {
        $tablename = $request->tablename;
        // モデル選択に渡す現在のテーブル名
        $selectedtable = $tablename;
        $modelindex = $this->sessionservice->getSession('modelindex');
        $modelselects = $this->sessionservice->getSession('modelselects', $modelindex);
        $cardcolumnsprop = null;
        if ($tablename) {
            $columnsprop = $this->sessionservice->getSession('columnsprop', $modelindex, $tablename);
            $cardcolumnsprop = $this->modelservice->arangeColumnspropToCard($columnsprop);    
        }
        $param = [
            'selectedtable' => $selectedtable,
            'modelselects'  => $modelselects,
            'cardcolumnsprop'  => $cardcolumnsprop,
        ];
        return $param;
    }

    // List表示用のパラメータを取得する
    public function getListParams($request) {
        $tablename = $request->tablename;
        $modelindex = $this->sessionservice->getSession('modelindex');
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
            // リストの行数
            $paginatecnt = $this->sessionservice->getSession('paginatecnt');
            if (!$paginatecnt) {$paginatecnt = 15;}
            // 成功メッセージ
            $success = $request->success !== '' ? $request->success : '';
            // 表示リストの詳細
            // テーブル名が更新されている時は既存のsessionを消す
            $lasttablename = $this->sessionservice->getSession('tablename');
            if ($lasttablename !== $tablename) {
                $this->sessionservice->forgetSession('tablename');
                $this->sessionservice->forgetSession('columnsprop');
                $this->sessionservice->forgetSession('lastsort');
                $this->sessionservice->forgetSession('page');
            }
            // 表示用のカラム名とプロパティ
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
        // テーブル名が更新されている時は既存の$columnspropを消す
        $lasttablename = $this->sessionservice->getSession('tablename');
        if ($lasttablename !== $tablename) {$this->sessionservice->forgetSession('columnsprop');}
        $columnsprop = $this->sessionservice->getSession('columnsprop', $modelindex, $tablename);
        $id = $request->id;
        // 成功メッセージ
        $success = $request->success !== '' ? $request->success : '';
        // テーブルの和名
        $tablecomment = $modelindex[$tablename]['tablecomment'];
        // columnspropのforeign_referenceをcard表示用に合体する
        $cardcolumnsprop = $this->modelservice->arangeColumnspropToCard($columnsprop);
        // foreignkey用のセレクトリストを用意する
        $foreignselects = $this->dbioservice->getForeginSelects($columnsprop);
        $page = $this->sessionservice->getSession('page');
        if ($mode !== 'create') {
            // 新規作成以外では表示するレコードの実体を取得する
            $row = $this->dbioservice->getRowById($request, $modelindex, $columnsprop, $id);
        } else {
            // 空のレコードデータを作る
            $row = $this->modelservice->getEmptyRow($cardcolumnsprop);
        }
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

    //
    public function killMyfile() {
        $userid = Auth::id();
        $this->commonservice->killMyfile($userid);
    }
}

