<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// TableCaseではTable表示に必要なデータの実体を取得する

declare(strict_types=1);
namespace App\Usecases\Table;

use App\Services\CommonService;
use App\Services\DbioService;
use App\Services\SessionService;
use App\Services\Table\SortService;
use App\Services\Table\ModelService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;
use SplFileObject;

class TableCase  {

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
    
    // Upload画面表示に必要なパラメータを準備する
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

    // upload実行処理
    public function csvUpload($request, $csvmode) {
        $uploadresult = [
            'errormsg' => '',
            'csvmode'  => $csvmode,
        ];
        $is_insertonly = $request->is_insertonly;   // 「新規のみ」チェックボックス
        $is_allowforeigninsert = $request->is_allowforeigninsert;   // 「参照元の更新を許可する」チェックボックス
        if ($csvmode == 'csvselect') {      // 1.最初のCSVファイル選択処理
            return $uploadresult;
        } else {                            // ファイル検証、又は登録実行処理
            $can_gosave = true;             // $csvmodeを'csvsave'に進めて良いかどうか
            // ロケールを日本語に設定
            setlocale(LC_ALL, 'ja_JP.UTF-8');
            // 他のユーザーとの重複を避けるためにユーザーidをファイル名に追加する
            // ★要検討：メンバーidの方が良いかも
            $tablename = $request->tablename;
            $userid = Auth::id();
            $savedfilename = $tablename.'_upload_'.strval($userid).'.csv';
            // アップロードしたファイルを取得
            if ($csvmode == 'csvcheck') {   // 2.チェックモードの最初にファイル保存
                // 'upload_file' はビューの inputタグのname属性
                $uploaded_file = $request->file('upload_file');
                if ($uploaded_file !== null) {
                    $uploaded_file->storeAs('public/csv/', $savedfilename);
                }
            }
            // ファイルが保存されているか確認する
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
            $csverrors = [];    // errorメッセージ
            $rawcolumns = [];   // uploadファイルのカラムリスト
            $iddictionary = $this->sessionservice->getSession('iddictionary');   // テーブル参照idリスト
            if (empty($iddictionary)) {$iddictionary = [];}
            $rawform = [];      // uploadされたままの値リスト
            $form =[];          // 更新用に加工済の値リスト
            foreach ($file as $row) {
                // 最終行の処理(最終行が空っぽの場合の対策
                if ($row == [null]) continue; 
                if ($row_count == 1) {          // 1行目はテーブル名
                    if ($row[0] !== $tablename) {   // $requestと一致しなければ処理を終了する
                        $uploadresult['errormsg'] = 'ファイルの内容が不正です';
                        return $uploadresult;
                    }
                } elseif ($row_count == 2) {    // 2行目はカラム名リスト
                    $rawcolumns = $row;
                } else {                        // 3行目以後がデータ
                    // uplaodされたままの値を$rawformに入れる
                    $colcnt = 0;
                    foreach ($row AS $columnvalue) {
                        // CSVの文字コードがSJISなのでUTF-8に変更
                        $rawform[$rawcolumns[$colcnt]] = mb_convert_encoding($columnvalue, 'UTF-8', 'SJIS');
                        $colcnt += 1;
                    }
                    // 行内のテーブル参照情報取得
                    $foreginkeys = $this->getForeginkeysForUpload($rawform);
                    if ($foreginkeys) {
                        // 参照先の実体を確認する
                        $foreginid = null;
                        foreach ($foreginkeys as $foreginkey) {
                            // テーブル参照値から参照テーブルidを取得
                            // $iddictionary内にあるか確認
                            if (array_key_exists($foreginkey, $iddictionary)) {
                                $foreginid = $iddictionary[$foreginkey];
                            } else {
                                // 未登録の参照を$iddictionaryに追加する
                                $foreginid = $this->dbioservice->findId($foreginkey);
                                $iddictionary[$foreginkey] = $foreginid;
                            }
                            if (!$foreginid) {
                                if ($is_allowforeigninsert) {
                                    if ($csvmode == 'csvcheck') {       // 2.チェックモード
                                        $csverrors[] = strval($row_count-2).':△'.$foreginkey.' の登録を試みます';
                                    } elseif ($csvmode == 'csvsave') {  // 3.登録実行モード
                                        // ★未実装：参照元更新が許可されていれば処理
                                        $errortips = $this->foreignkeyInsert($foreginkey);                                        
                                        if ($errortips !== null && $errortips !== true) {
                                            $csverrors[] = strval($row_count-2).':▼'.$this->errortipsTotext($errortips);
                                            $uploadresult += [
                                                'tablename' => $tablename,
                                                'row_count' => $row_count,
                                            ];
                                            return $uploadresult;                                            
                                        }                                      
                                    }
                                } else {
                                    $csverrors[] = strval($row_count-2).':▼'.$foreginkey.' は未登録の参照です';
                                    $can_gosave = false;
                                }
                            }
                        }
                    }           
                    // テーブルに登録できる値リストに更新
                    $modelindex = $this->sessionservice->getSession('modelindex');                       
                    $form = $this->modelservice->arangeForm($tablename, $rawform, $foreginkeys, $iddictionary);
                    // validation
                    if ($is_insertonly) {     // 全て新規なので$idはnull
                        $id = null;
                    } else {                            // $formの$idを探しに行く
                        // $form内のuniqueKeyの値を取得
                        $findkey = $this->getFindkeyForUpload($modelindex, $tablename, $form);
                        $id = $this->dbioservice->findId($findkey);
                        if ($id == 'many') {
                            $csverrors[] = strval($row_count-2).':▼'.$findkey.' は複数の行を変更します';
                            $can_gosave = false;
                        }
                    }
                    // '登録者・更新者'の値を入れる
                    $mode = !$id ? 'store' : 'update';
                    $form = $this->commonservice->addBytoForm($rawcolumns, $form, $mode);
                    if ($csvmode == 'csvcheck') {       // 2.チェックモード
                        $mode = 'check';
                        $form = $this->commonservice->addBytoForm($rawcolumns, $form, $mode);
                        $errortips = $this->dbioservice->excuteCsvprocess($tablename, $form, $id, $mode);
                        if ($errortips !== null) {
                            $csverrors[] = strval($row_count-2).':▼'.$this->errortipsTotext($errortips);
                            $can_gosave = false;
                        }
                    } elseif ($csvmode == 'csvsave') {  // 3.登録実行モード
                        // 登録実行
                        $mode = 'save';
                        $errortips = $this->dbioservice->excuteCsvprocess($tablename, $form, $id, $mode);
                        if ($errortips !== null && $errortips !== true) {
                            $csverrors[] = strval($row_count-2).':▼'.$this->errortipsTotext($errortips);
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
        // 後処理
        $treatedcount = $row_count -3;      // 処理された行数（全行数+1 -ファイル名行 -カラム名行
        if ($csvmode == 'csvcheck') {       // 2.チェックモード
            $this->sessionservice->putSession('iddictionary', $iddictionary);   // テーブル参照id辞書保存
            $uploadresult['csverrors'] = $csverrors;
            if (count($csverrors) == 0) {
                $uploadresult['success'] = $treatedcount.'件 エラーはありませんでした';
            }
            if ($can_gosave) {
                $uploadresult['csvmode'] = 'csvsave';   // エラーが無い時だけ 3.登録実行モードへ進む
            }
        } elseif ($csvmode == 'csvsave') {  // 3.登録実行モード
            if (count($csverrors) == 0) {
                $uploadresult['success'] = $treatedcount.'件 登録しました';
            }
            $this->sessionservice->forgetSession('iddictionary');   // テーブル参照id辞書削除
            $this->killMyfile($userid);     // strage/app/public/csv内の自分のファイル削除
        }
        $uploadresult += [
            'tablename' => $tablename,
            'treatedcount' => $treatedcount,
        ];
        return $uploadresult;
    }

    // ★ここがちゃんと動くかチェックだ！！！！！！！！！！！
    // 未登録のforreignkeyをinsertする
    private function foreignkeyInsert($foreginkey) {
        $errortips = [];
        $tablename = Str::before('?', $foreginkey);
        $form =[];
        $rawcombinations = explode('&&', Str::after('?', $foreginkey));
        $rawcombinations += explode('&', Str::after('?', $foreginkey));
        foreach ($rawcombinations as $rawcombination) {
            $form[Str::before('=', $rawcombination)] = Str::after('=', $rawcombination);
        }
        $id = null;
        $mode = 'save';
        $errortips = $this->dbioservice->excuteCsvprocess($tablename, $form, $id, $mode);
        return $errortips;
    }

    // validationからの戻り値をTextに替える
    private function errortipsTotext($errortips) {
        $errortip = '';
        if ($errortips !== null) {
            foreach ($errortips as $key => $errors) {
                $errortip .= implode( ',', array_values($errors));
            }
        }
        return $errortip;      
    }

    /* $formのデータで$idを取得するためのkey作成
    参照テーブル名?参照カラム名=値&参照カラム名=値
    ※複合カラムユニークと、カラム毎のユニークを区別するためにセパレータを変える
    　複合カラムの場合は &&、カラム毎の場合は &
    */
    private function getFindkeyForUpload($modelindex, $tablename, $form) {
        // テーブルのユニークキーを取得
        $findkey = null;
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
                $subcnt = 1;
                foreach ($uniquekey as $colname){
                    if (array_key_exists($colname, $form)) {
                        if ($subcnt > 1) {
                            $findkey .= $separator;
                        }
                        $findkey .= $colname.'='.$form[$colname];
                    }
                    $subcnt += 1;
                }
                $cnt +=1;
            }
        }    
        return $findkey;
    }

    // $foreginkeys = [参照テーブル名?参照カラム名=値&参照カラム名=値,]
    private function getForeginkeysForUpload($rawform) {
        $foreginkeys =[];
        $findidset = $this->getForeginFindidsetForUpload($rawform);
        $modelindex = $this->sessionservice->getSession('modelindex');
        foreach ($findidset as $foregintablename => $colandvalue) {
            $foreginkey = $this->getFindkeyForUpload($modelindex, $foregintablename, $colandvalue);
            $foreginkeys[] = $foreginkey;
        }
        return $foreginkeys;
    }

    // $findidset = [参照テーブル名 => [参照カラム名 => 値, 参照カラム名 => 値,],]
    private function getForeginFindidsetForUpload($rawform) {
        $findidset = [];
        foreach ($rawform as $columnname => $value) {
            if (strripos($columnname, '_id_') && substr($columnname, -7) !== '_id_2nd' && $value !== '') {
                // 一番後ろのテーブル名とカラム名
                $foregintablename = substr($columnname, 0, strripos($columnname, '_id_'));
                $foregincolname = substr($columnname, strripos($columnname, '_id_') + 4);
                if (strripos($foregintablename, '_id_')) {
                    $foregintablename = substr($foregintablename, strripos($foregintablename, '_id_') + 4);
                }
                if (substr($foregintablename, 0, 4) == '2nd_') {
                    $foregintablename = substr($foregintablename, 4);
                }
                if (substr($foregincolname, 0, 4) == '2nd_') {
                    $foregincolname = substr($foregincolname, 4);
                }
                $foregintablename = Str::plural($foregintablename);
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
        // 現在のテーブル名をSessionに保存する
        $this->sessionservice->putSession('tablename', $tablename);
        // モデル選択に渡す現在のテーブル名
        $selectedtable = $tablename;
        $modelindex = $this->sessionservice->getSession('modelindex');
        $modelselects = $this->sessionservice->getSession('modelselects', $modelindex);
        // search用の変数
        $cardcolumnsprop = null;
        $searchinput = [];
        $searcherrors = null;
        $foreignselects = null;
        if ($tablename) {
            $columnsprop = $this->sessionservice->getSession('columnsprop', $modelindex, $tablename);
            $cardcolumnsprop = $this->modelservice->arangeColumnspropToCard($columnsprop);
            $searchinput = $this->setSerachinput($request);
            $searcherrors  =$this->validateSerch($tablename, $columnsprop, $searchinput);
            $foreignselects = $this->dbioservice->getForeginSelects($columnsprop);    
        }
        $param = [
            'selectedtable' => $selectedtable,
            'modelselects'  => $modelselects,
            'cardcolumnsprop'   => $cardcolumnsprop,
            'searchinput'       => $searchinput,
            'searcherrors'  => $searcherrors,
            'foreignselects'    => $foreignselects,
        ];
        return $param;
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
        if (count($searchinput) > 0) {  // 
            $this->sessionservice->putSession('searchinput', $searchinput);
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
        $id = null;
        $mode = "check";
        $form = $this->modelservice->getFormforSearch($withhearder, $columnsprop, $searchinput);
        $errortips = $this->dbioservice->excuteCsvprocess($tablename, $form, $id, $mode);
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

    // List表示用のパラメータを取得する
    public function getListParams($request, $params = null) {
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
        $searchinput = $params['searchinput'];
        if (array_key_exists($tablename, $modelindex)) {
            // リストの行数
            $paginatecnt = $this->sessionservice->getSession('paginatecnt');
            if (!$paginatecnt) {$paginatecnt = 15;}
            // 成功メッセージ
            $success = $request->success !== '' ? $request->success : '';
            // 表示リストの詳細
            // 表示用のカラム名とプロパティ
            $columnsprop = $this->sessionservice->getSession('columnsprop', $modelindex, $tablename);
            // テーブルの和名
            $tablecomment = $modelindex[$tablename]['tablecomment'];
            // 作業用に指定が必要な場合のソート順（ここでは既存テーブルの参照なので不要）
            $tasksort = null;
            // Listのソート順を取得する
            $tempsort = $this->sortservice->getTempsort($request, $modelindex, $columnsprop, $tasksort);
            // 表示するListの実体を取得する
            $is_pagerequest = $this->getIspagerequest($request);
            if ($is_pagerequest) {
                $searchinput = $this->sessionservice->getSession('searchinput');
                $searchinput = is_null($searchinput) ? [] : $searchinput;
            }
            $rows = $this->dbioservice->getRows($request, $modelindex, $columnsprop, $searchinput, $paginatecnt, $tempsort);
            // 今回ソートの先頭部分を「行表示から戻ってきた時」のためにSessionに保存する
            $lastsort = ($tempsort ? array_key_first($tempsort).'--'.array_values($tempsort)[0] : '');
            $this->sessionservice->putSession('lastsort', $lastsort);
            // 現在のページを「行表示から戻ってきた時」のためにSessionに保存する
            $this->sessionservice->putSession('page', $page);
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

    // Card表示用のパラメータを取得する
    public function getCardParams($request, $mode){
        $tablename = $request->tablename;
        $modelindex = $this->sessionservice->getSession('modelindex');
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
        $tempsql = $this->sessionservice->getSession('tempsql');
        $rows = $this->dbioservice->getRowsByRawsql($tempsql);
        foreach ($rows as $row) {
            $values = [];
            foreach ($columnsprop as $columnname => $pops) {
                $rowvalue = str_replace(array('\r\n','\r','\n',',',chr(10)), '', $row->$columnname);
                $rowvalue = str_replace('\n', '\\n', $rowvalue);
                // $rowvalue = str_replace(',', '', $rowvalue);
                array_push($values, $rowvalue);
            }
            $downloadcsv[]= $values;
        }
        return $downloadcsv;
    }

    // wnetapp\storage\app\public\csv内の$useridがuploadしたファイルを削除する
    public function killMyfile() {
        $userid = Auth::id();
        $files = Storage::allFiles('public/csv/');;
        foreach ($files as $file) {
            if (strpos($file,'_'.strval($userid).'.csv') !== false) {
                Storage::delete($file);
            }
        }
    }

    // wnetapp\storage\app\public\csv内の10分以上前にuploadされたファイルを削除する
    public function killOldfile() {
        $setminuts = 10;
        $files = Storage::allFiles('public/csv/');;
        foreach ($files as $file) {
            $updatedtime = Storage::lastModified($file);;
            if ((time()-$updatedtime)/60 > $setminuts && substr($file, -4) == '.csv') {
                Storage::delete($file);
            }
        }
    }
    
    // $requestの状態からSessionを適正化する
    public function sessionOptimaize($request) {
        // テーブル名が更新されている時は既存のtable関連Sessionを消す
        $tablename = $request->tablename;
        $lasttablename = $this->sessionservice->getSession('tablename');
        if ($lasttablename !== $tablename) {
            $this->sessionservice->putSession('tablename', $tablename);
            $this->sessionservice->forgetSession('columnsprop');
            $this->sessionservice->forgetSession('searchinput');
            $this->sessionservice->forgetSession('lastsort');
            $this->sessionservice->forgetSession('page');
        }
    }
    
    // requestをtableに登録可能な配列に替える
    public function getForm($request, $mode) {
        $form = [];
        $tablename = $request->tablename;
        $rawform = $request->all();
        $columnnames = Schema::getColumnListing($tablename);
        foreach ($rawform as $key => $value) {
            if ($mode == 'store' && $key == 'id') {
                // store時のidは除外
            } elseif (in_array($key, $columnnames) && substr($key,-3) !== '_at') {
                $form[$key] = $value;
            }
        }
        $form = $this->commonservice->addBytoForm($columnnames, $form, $mode);
        return $form;
    }

    // 汎用の登録・更新プロセス
    public function excuteProcess($tablename, $form, $id) {
        $createdid = $this->dbioservice->excuteProcess($tablename, $form, $id);
        return $createdid;
    }

    // 削除更新(softDelete)実行
    public function is_Deleted($tablename, $id) {
        return $this->dbioservice->is_Deleted($tablename, $id);
    }

    // 完全削除実行
    public function is_forceDeleted($tablename, $id) {
        return $this->dbioservice->is_forceDeleted($tablename, $id);
    }

    // 復活実行
    public function is_Restored($tablename, $id) {
        return $this->dbioservice->is_Restored($tablename, $id);
    }

}

