<?php
declare(strict_types=1);
namespace App\Usecases\Table;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Services\SessionService;
use App\Services\Database\Get_ByNameService;
use App\Services\Database\Add_ByNameToFormService;
use App\Services\Database\AddIddictionaryService;
use App\Services\Database\ExcuteCsvprocessService;
use App\Services\Database\FindValueService;
use App\Services\File\KillMyFileService;
use App\Services\Table\OpimizeRawformWithIddictionaryService;
use App\Services\Model\GetModelSelectParamsService;

use SplFileObject;

class CsvUploadCase {

    private $sessionservice;
    public function __construct(
        SessionService $sessionservice,
        ExcuteCsvprocessService $excutecsvprocessservice
        ) {
            $this->sessionservice = $sessionservice;
            $this->excutecsvprocessservice = $excutecsvprocessservice;
    }
    
    // 指定Excelシートから出力したしたCSVファイルをアップロードする
    public function getParams($request, $csvmode) {
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
        $upload = [];
        // ファイル選択済みなら値を得て処理する
        if ($csvmode !== 'csvselect') {
            $upload = $this->doUpload($request, $csvmode);
        }
        // Upload画面表示に必要なパラメータを準備する
        $params += $this->getUploadParams($request, $upload);
        return $params;
    }

    // upload実行処理 ★★ 整理するのに時間がかかりすぎるので、全体が出来てから再着手のこと
        /*
        $csvmode
        'csvselect'     1.CSVファイルを選択する
        'csvcheck'      2.選択したファイルをチェックする
        'csvsave'       3.ファイルをテーブルに登録する
        */
    public function doUpload($request, $csvmode) {
        $upload = [
            'danger' => '',
            'csvmode'  => $csvmode,
        ];
        $is_insertonly = $request->is_insertonly;                   // 「新規のみ」チェックボックス
        $is_allowforeigninsert = $request->is_allowforeigninsert;   // 「参照元の更新を許可する」チェックボックス
        if ($csvmode == 'csvselect') {      // 1.最初のCSVファイル選択処理
            return $upload;
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
            if(!file_exists(storage_path('app/public/csv/'.$savedfilename ))) {
                $upload['danger'] = 'ファイルが選択されていません';
                return $upload;        
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
            $rawform = [];      // uploadされたままの値リスト
            $form = [];         // 更新用に加工済の値リスト
            $uniquekeys = $this->getUniquekeys($tablename);   // テーブルのuniquekeys
            $foreinkeycolumns = [];
            foreach ($file as $row) {
                // 最終行の処理(最終行が空っぽの場合の対策
                if ($row == [null]) continue; 
                if ($row_count == 1) {          // 1行目はテーブル名
                    if ($row[0] !== $tablename) {   // $requestと一致しなければ処理を終了する
                        $upload['danger'] = 'ファイルの内容が不正です';
                        return $upload;
                    }
                } elseif ($row_count == 2) {    // 2行目はカラム名リスト
                    $rawcolumns = $row;
                    $foreinkeycolumns = $this->getForeinKeyColumns($rawcolumns, $uniquekeys);
                } else {                        // 3行目以後がデータ
                    // uplaodされたままの値を$rawformに入れる
                    $rawform = $this->convertToUtf($rawcolumns, $row);
                    // updateに必要な参照idを得る
                    $rawform = $this->addForeignId($rawform, $foreinkeycolumns, $iddictionary);
                    // テーブルに登録できる値リストに更新
                    $opimizerawformwithiddictionaryservice = new OpimizeRawformWithIddictionaryService;
                    $form = $opimizerawformwithiddictionaryservice
                    ->opimizeRawformWithIddictionary($tablename, $rawform, $foreinkeycolumns, $iddictionary);
                    $get_bynameservice = new Get_ByNameService;
                    $add_bynametoformservice = new Add_ByNameToFormService;
                    // validation
                    if ($is_insertonly) {     // 全て新規なので$idはnull
                        $id = 0;
                    } else {                            // $formの$idを探しに行く
                        // $form内のuniqueKeyの値を取得
                        $modelindex = $this->sessionservice->getSession('modelindex');
                        $findkey = $this->getFindkeyForUpload($modelindex, $tablename, $form);
                        $findvalueservice  = new FindValueService;
                        $id = $findvalueservice->findValue($findkey, 'id');
                        if ($id != 0 && $id == 'many') {
                            $csverrors[] = strval($row_count-2).':▼'.$findkey.' は複数の行を変更します';
                            $can_gosave = false;
                        }
                    }
                    // '登録者・更新者'の値を入れる
                    $mode = $id == 0 ? 'store' : 'update';
                    $byname = $get_bynameservice->Get_ByName();
                    $form = $add_bynametoformservice->Add_ByNameToForm($byname, $form, $mode, $rawcolumns);
                    if ($csvmode == 'csvcheck') {       // 2.チェックモード
                        $mode = 'check';
                        $form = $add_bynametoformservice->Add_ByNameToForm($byname, $form, $mode, $rawcolumns);
                        $errortips = $this->excutecsvprocessservice->excuteCsvprocess($tablename, $form, $id, $mode);
                        if ($errortips !== null) {
                            $csverrors[] = strval($row_count-2).':▼'.$this->errortipsTotext($errortips);
                            $can_gosave = false;
                        }
                    } elseif ($csvmode == 'csvsave') {  // 3.登録実行モード
                        // 登録実行
                        $mode = 'save';
                        $errortips = $this->excutecsvprocessservice->excuteCsvprocess($tablename, $form, $id, $mode);
                        if ($errortips !== null && $errortips !== true) {
                            $csverrors[] = strval($row_count-2).':▼'.$this->errortipsTotext($errortips);
                            $upload += [
                                'tablename' => $tablename,
                                'row_count' => $row_count,
                            ];
                            return $upload;                                            
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
            $upload['csverrors'] = $csverrors;
            if (count($csverrors) == 0) {
                $upload['success'] = $treatedcount.'件 エラーはありませんでした';
            }
            if ($can_gosave) {
                $upload['csvmode'] = 'csvsave';   // エラーが無い時だけ 3.登録実行モードへ進む
            }
        } elseif ($csvmode == 'csvsave') {  // 3.登録実行モード
            if (count($csverrors) == 0) {
                $upload['success'] = $treatedcount.'件 登録しました';
            }
            $this->sessionservice->forgetSession('iddictionary');   // テーブル参照id辞書削除
            $killmyfileservice = new KillMyFileService;
            $killmyfileservice->killMyFile();     // strage/app/public/csv内の自分のファイル削除
        }
        $upload += [
            'tablename' => $tablename,
            'treatedcount' => $treatedcount,
        ];
        return $upload;
    }

    private function convertToUtf($rawcolumns, $row) {
        $colcnt = 0;
        foreach ($row AS $columnvalue) {
            // CSVの文字コードがSJISなのでUTF-8に変更
            $rawform[$rawcolumns[$colcnt]] = mb_convert_encoding($columnvalue, 'UTF-8', 'SJIS');
            $colcnt += 1;
        }
        return $rawform;
    }    
 
    private function getUniquekeys($tablename) {
        $modelindex = $this->sessionservice->getSession('modelindex');
        $uniquekeys = $modelindex[$tablename]['modelname']::$uniquekeys;
        return $uniquekeys;
    }

    private function getForeinKeyColumns($rawcolumns, $uniquekeys) {
        $foreinkeycolumns = [];
        foreach ($uniquekeys as $uniquekey) {
            foreach ($uniquekey as $uniquekeycolumn) {
                foreach ($rawcolumns as $rawcolumn) {
                    // 参照値の$columunameならば
                    if (strripos($rawcolumn, $uniquekeycolumn) !== false 
                        && strripos($rawcolumn, '_id_') 
                        && substr($rawcolumn, -7) !== '_id_2nd') {
                        // 一番後ろのテーブル名とカラム名
                        $foregintablename = $this->getForeginTablenameFromColumnname($rawcolumn);
                        $foregincolname = $this-> getForeginColnameFromColumnname($rawcolumn);
                        $foreignuniquekeys = $this->getUniquekeys($foregintablename);
                        foreach ($foreignuniquekeys as $foreginuniquekey) {
                            if (in_array($foregincolname, $foreginuniquekey)) {
                                $foreinkeycolumns[$rawcolumn] = [$foregintablename => $foregincolname];
                            }                               
                        }            
                    }
                }
            }
        }
        return $foreinkeycolumns;
    }

    private function addForeignId($rawform, $foreignkeycolumns) {
        // 参照Tableとuniquekey、&種類
        $iddictionary = $this->sessionservice->getSession('iddictionary');
        $foreigntablenames = [];
        foreach ($foreignkeycolumns as $foreinkeycolumn => $param) {
            $uniquekeys = [];
            if (!in_array(key($param), $foreigntablenames)) {
                $foreigntablename = key($param);
                $foreignuniquekeys = $this->getUniquekeys($foreigntablename);
                if (count($foreignuniquekeys) == 1) {
                    $andseparater = '&&';
                    $uniquekeys = $foreignuniquekeys[0];
                } else {
                    $andseparater = '&';
                    foreach ($foreignuniquekeys as $foreignuniquekey) {
                        $uniquekeys[] = $foreignuniquekey;
                    }
                }
                $foreigntablenames[$foreigntablename] = [
                    'andseparater'  => $andseparater,
                    'uniquekeys'    => $uniquekeys,
                    'findvalue'     => false,
                ];
            }
        }
        // 他のidが見つからないと、見つけられないidがある場合のために繰り返す
        for ($i = 1; $i <= count($foreignkeycolumns); $i++) {
            foreach ($foreigntablenames as $foreigntablename => $serchparams) {
                if ($serchparams['findvalue'] == true) { continue;}
                // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
                $foreginkey = $foreigntablename.'?';
                foreach ($serchparams['uniquekeys'] as $uniquekey) {
                    $foundvalue = null;
                    foreach ($foreignkeycolumns as $foreignkeycolumn => $columnparam) {
                        if (key($columnparam) == $foreigntablename && current($columnparam) == $uniquekey) {
                            $foundvalue = $rawform[$foreignkeycolumn];
                            $serchparams['findvalue'] = true;
                        }
                    }
                    if (!$foundvalue && substr($uniquekey, -3) == '_id' && key_exists($uniquekey, $rawform)) {
                        $foundvalue = $rawform[$uniquekey];
                        $serchparams['findvalue'] = true;
                    }
                    $foreginkey .= $uniquekey.'='.$foundvalue.$andseparater;                   
                }
                if ($serchparams['findvalue'] == true) {
                    $foreginkey = substr($foreginkey, 0, -strlen($andseparater));
                    $addiddictionaryservice = new AddIddictionaryService;
                    $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
                    $rawform[Str::singular($foreigntablename).'_id'] = $iddictionary[$foreginkey];
                }
            }
        }
        return $rawform;
    }

    private function getForeginTablenameFromColumnname($columnname) {
        $foregintablename = substr($columnname, 0, strripos($columnname, '_id_'));
        if (strripos($foregintablename, '_id_')) {
            $foregintablename = substr($foregintablename, strripos($foregintablename, '_id_') + 4);
        }
        if (substr($foregintablename, 0, 4) == '2nd_') {
            $foregintablename = substr($foregintablename, 4);
        }
        $foregintablename = Str::plural($foregintablename);
        return $foregintablename ;
    }
    
    private function getForeginColnameFromColumnname($columnname) {
        $foregincolname = substr($columnname, strripos($columnname, '_id_') + 4);
        if (substr($foregincolname, 0, 4) == '2nd_') {
            $foregincolname = substr($foregincolname, 4);
        }
        return $foregincolname ;
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
            $form[Str::before('=', $rawcombination)] = Str::after('=', urldecode($rawcombination));
        }
        $id = null;
        $mode = 'save';
        $errortips = $this->excutecsvprocessservice->excuteCsvprocess($tablename, $form, $id, $mode);
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
        $findkey = 0;
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
                foreach ($uniquekey as $colname) {
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

    // Upload画面表示に必要なパラメータを準備する
    private function getUploadParams($request, $upload=[]) {
        $csvmode = $request->csvmode;
        $tablename = $request->tablename;
        $success = $request->success !== '' ? $request->success : '';
        $success = array_key_exists('success', $upload) ? $upload['success'] : $success ;
        $csvmode = array_key_exists('csvmode', $upload) ? $upload['csvmode'] : $csvmode;
        $danger = array_key_exists('danger', $upload) ? $upload['danger'] : '' ;
        $csverrors = array_key_exists('csverrors', $upload) ? $upload['csverrors'] : [] ;
        // テーブルの和名
        $modelindex = $this->sessionservice->getSession('modelindex');
        $tablecomment = $modelindex[$tablename]['tablecomment'];
        $params = [
            'mode'          => $csvmode,
            'tablename'     => $tablename,
            'tablecomment'  => $tablecomment,
            'tgtuploadfile' => $tablecomment.'_upload.csv',
            'success'       => $success,
            'danger'        => $danger,
            'csverrors'     => $csverrors,
        ];
        return $params;
    }

    public function killMyfile()
    {
        $killmyfileservice = new KillMyFileService;
        $killmyfileservice->killMyFile();
    }
}

