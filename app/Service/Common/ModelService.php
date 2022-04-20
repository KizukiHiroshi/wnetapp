<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Modelから取得したデータを整理する

declare(strict_types=1);
namespace App\Service\Common;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ModelService {

    public function __construct() {
    }

    /* modelindex:全てのモデルの一覧
    tablename => [         // テーブルの物理名
        'modelname' => '',            // モデル名        
        'modelzone' => '',            // モデルの分類名
        'tablecomment' => '',         // テーブルの和名
    ]
        'referencedcolumns' => '',    // 被参照カラム    
        'defaultsort' => '',          // ソート順
        'validationrule' => ''        // バリデーションルール
        は、
        $modelname::$referencedcolumns,    
        $modelname::$defaultsort,    
        $modelname::$validationrule,
        で、取得すること  
    */
    public function getModelindex() {
        $modelindex = []; // 返すモデルリスト
        // 拡張子を着けてディレクトリ名でヒットしないようにする
        // Models直下のモデル検索する
        $dir = __DIR__;
        $dir = str_replace('Service\Common', 'Models', $dir);
        $modeldirs = glob($dir.'/*');
        foreach($modeldirs as $modeldir) {
            if (strpos($modeldir, '.php')) {
                $modelindex = $this->addModels($modelindex, $modeldir);
            } else {
                $indirmodelindex = glob($modeldir . '/*');
                foreach($indirmodelindex as $indirmodel) {
                    if (strpos($indirmodel, '.php')) {
                        $modelindex = $this->addModels($modelindex, $indirmodel);
                    }
                }
            }
        }
        $modelzone  = array_column($modelindex, 'modelzone');
        $tablecomment  = array_column($modelindex, 'tablecomment');
        array_multisort($modelzone, SORT_ASC, $tablecomment, SORT_ASC, $modelindex);               
        return $modelindex;
    }

    private function addModels($modelindex, $modelname) {
        // Modelnameに余分な文字を削除する
        $modelname = $this->organizeModelname($modelname);
        // table名に余分な文字を削除する
        $tablename = $this->ModelnameToTablename($modelname);
        $modelindex[$tablename] = [
            'modelname' => $modelname,
            'modelzone' => $modelname::$modelzone,
            'tablecomment' => $modelname::$tablecomment,
        ];
        return $modelindex;
    }
    
    private function organizeModelname($modelname) {
        // Modelnameに余分な文字を削除する
        $modelname = substr($modelname,strpos($modelname,'Models'));
        $modelname = 'App/'.$modelname;
        $modelname = str_replace('.php', '', $modelname);
        $modelname = str_replace('/', '\\', $modelname);
        return $modelname;
    }    
    private function ModelnameToTablename($modelname) {
        // table名に余分な文字を削除する
        $tablename = Str::afterLast($modelname, '\\');
        $tablename = Str::plural(Str::lower($tablename));
        return $tablename;
    }

    /* getModelselects:モデル選択用のmodelzone,tablecommentのグループセレクト配列
    tablename => [         // テーブルの物理名
        'group'     => modelzone        // テーブルの属するゾーン
        'value'     => tablecomment,    // テーブル和名        
    ] */
    public function getModelselects($modelindex) {
        $modelselects = []; // 返すグループセレクト配列名
        foreach($modelindex as $key => $model) {
            $modelselects[$key] = [
                'group' => $model['modelzone'],
                'value' => $model['tablecomment'],
            ];
        }
        return $modelselects;
    }

    /* 表示カラムの一覧:$columnsprop
    ['columnname' =>   // 表示カラム名 ★参照カラムの場合はforeign_id_?????(referensdcolum)
        [
            'tablename' => '',  // 実テーブル名:参照カラムで参照テーブル名
            'type'      => '',  // 変数タイプ
            'length'    => '',  // 変数の長さ
            'comment'   => '',  // カラムの和名
            'notnull'   => '',  // NULL許可   
            'default'   => '',  // 初期値
            'realcolumn'=> '',  // 実カラム名:参照カラムで参照テーブル状のカラム名
            'sortcolumn'=> '',  // ソート時に使う実カラム名：'name'に対する'name_kana'
        ],
    ]
    */
    public function getColumnsProp($modelindex, $tablename) {
        $columns = DB::select('show full columns from '.$tablename);
        $columnsprop = [];
        // テーブルのuniquekey取得
        $model = $modelindex[$tablename];
        $uniquekeys = $model['modelname']::$uniquekeys;
        $uniquekeystr = '';
        foreach ($uniquekeys as $key => $uniquekey) {
            $uniquekeystr .= implode(',', $uniquekey);
        }
        $uniquekeys = explode(',', $uniquekeystr);
        foreach ($columns as $column) {
            $columnname = $column->Field;
            $sortcolumn = $columnname;
            $isunique = in_array($columnname, $uniquekeys) ? TRUE : NULL;
            // foreign_idの場合
            if (substr($columnname,-3) == '_id' || substr($columnname,-7) == '_id_2nd') {
                $refcolumnsprop = [];
                $refcolumnsprop = $this->delveId($refcolumnsprop, $modelindex, $tablename, $columnname);
                $refcolumnsprop = $this->sortRefcolumnsporp($refcolumnsprop);
                $columnsprop = array_merge($columnsprop, $refcolumnsprop);
            } else {
                $columnsprop[$columnname] = $this->getColumnProp($tablename, $columnname, $sortcolumn, $isunique);
            }
        }
        return $columnsprop;
    }

    // $refcolumnspropを参照の深い順に並べ替える
    private function sortRefcolumnsporp($refcolumnsprop) {
        $newarray = [];
        $sortarray = [];
        $keyarray = array_keys($refcolumnsprop);
        foreach($keyarray as $key) {
            if (substr($key, -3) == '_id') {
                $sortarray[$key] = substr_count($key, '_id') * 10;
            } else {
                $sortarray[$key] = substr_count($key, '_id') * 10 - 1;
            }
        }
        arsort($sortarray);
        foreach($sortarray as $key => $value) {
            $newarray[$key] = $refcolumnsprop[$key];
        }
        return $newarray;
    }

    // 参照キー(〇〇_id)の参照先を$columnspropに入れる再帰関数
    private function delveId ($refcolumnsprop, $modelindex, $tablename, $columnname) {
        // '_id_'が含まれていればそこまで消す
        if (strripos($columnname, '_id_')) {
            $realcolumnname = substr($columnname, strripos($columnname, '_id_') + 4);
        } else {
            $refcolumnsprop[$columnname] = $this->getColumnProp($tablename, $columnname, $columnname);
            $realcolumnname = $columnname ;
        }
        $foreigntablename = Str::plural(Str::before($realcolumnname, '_id'));
        $foreignmodel = $modelindex[$foreigntablename];
        $referencedcolumnnames = $foreignmodel['modelname']::$referencedcolumns;
        $refcolumnname = '';
        foreach($referencedcolumnnames AS $referencedcolumnname) {
            $referencedsortcolumnname
                = $this->checkAlternativeSortColumn($referencedcolumnname, $foreigntablename);
            $newprop = [$columnname.'_'.$referencedcolumnname =>
                $this->getColumnProp($foreigntablename, $referencedcolumnname, $referencedsortcolumnname)];
            $refcolumnsprop = array_merge($refcolumnsprop, $newprop);
            if (substr($referencedcolumnname,-3) == '_id') {
                $refcolumnname = $columnname.'_'.$referencedcolumnname;
            }
        }
        if ($refcolumnname == '') {
            return $refcolumnsprop;
        } else {
            return $this->delveId ($refcolumnsprop, $modelindex, $foreigntablename, $refcolumnname);
        }
    }

    // $columnsprop取得
    private function getColumnProp($tablename, $realcolumn, $sortcolumn, $isunique = NULL) {
        $tgtschema = Schema::getConnection()->getDoctrineColumn($tablename, $realcolumn);
        $columnprop = [
            'tablename' => $tablename,
            'type'      => $tgtschema->getType()->getName(),
            'length'    => $tgtschema->toArray()['length'],
            'comment'   => $tgtschema->toArray()['comment'],
            'notnull'   => $tgtschema->toArray()['notnull'],
            'default'   => $tgtschema->toArray()['default'],
            'isunique'      => $isunique,
            'realcolumn'    => $realcolumn,
            'sortcolumn'    => $sortcolumn,
        ];
        return $columnprop;
    }

    // 代替ソートカラム名に替えるかチェックする
    private function checkAlternativeSortColumn($columnname, $tablename) {
        // ソートカラムとして入れ替えが必要なカラム名
        $alternativesortcolumns = [
            'name' => 'name_kana'
        ]; 
        // 入れ替え対象かどうか 
        if (array_key_exists($columnname, $alternativesortcolumns)) {
            // ターブルのカラム名取得
            $columnnames = Schema::getColumnListing($tablename);
            // テーブルに代替カラムが存在すれば入れ替える
            if (in_array($alternativesortcolumns[$columnname], $columnnames)) {
                return $alternativesortcolumns[$columnname];
            } else {
                return $columnname;
            }
        } else {
            return $columnname;           
        }
    }

    // columnspropのforeignをcard表示用に変更する
    public function arangeColumnspropToCard($columnsprop) {
        $cardcolumnsprop = [];
        $foreigncolumn = '';
        // $referencecolumns = $this->getReferenceColumns($columnsprop);
        foreach ($columnsprop AS $columnname => $prop) {
            if (strpos($columnname, '_id_') == false || substr($columnname, -3) =='_id' || substr($columnname, -7) =='_id_2nd') {
                $cardcolumnsprop[$columnname] = $prop;
                if (substr($columnname, -3) =='_id' || substr($columnname, -7) =='_id_2nd') {
                    $foreigncolumn = $columnname;
                    // foreign_idの後にreferenceを入れる
                    $prop['type'] = 'string';
                    $cardcolumnsprop[$foreigncolumn.'_reference'] = $prop;
                }
            } elseif (strpos($columnname, $foreigncolumn) !== false && strpos($columnname, '_id_') !== false) {
                if (intval($prop['length']) > 0) {
                    $length = intval($cardcolumnsprop[$foreigncolumn.'_reference']['length']);
                    $length +=intval($prop['length']);
                    $cardcolumnsprop[$foreigncolumn.'_reference']['length'] = strval($length);
                }
            }
        }
        return $cardcolumnsprop;
    }
    
    // 表示する行の空データを作る
    public function getEmptyRow($columnsprop) {
        $rawrow =[];
        foreach ($columnsprop AS $columnname => $value) {
            $rawrow[$columnname] = 'old('.$columnname.')';
        }
        $row = (object) $rawrow;
        return $row;
    }

    // uploadされたリストをiddictionary参照利用して登録可能な配列に替える
    public function arangeForm($tablename, $rawform, $foreginkeys, $iddictionary) {
        $form = [];
        $columnnames = Schema::getColumnListing($tablename);
        foreach ($rawform as $key => $value) {
            if (in_array($key, $columnnames) && substr($key,-3) !== '_at') {
                $form[$key] = $value;
            }
        }
        foreach ($foreginkeys as $foreginkey) {
            $foregintablename = Str::singular(Str::before($foreginkey,'?')).'_id';
            if (in_array($foregintablename, $columnnames)) {
                $form[$foregintablename] = $iddictionary[$foreginkey];
            }
        }
        return $form;
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
        $form = $this->addBytoForm($columnnames, $form, $mode);
        return $form;
    }

    // Formに_byを加える
    public function addBytoForm($columnnames, $form, $mode) {
        $username = Auth::user()->name;
        if (in_array('created_by', $columnnames) && $mode == 'store') {
            $form['created_by'] = $username;
        }        
        if (in_array('updated_by', $columnnames)) {
            $form['updated_by'] = $username;
        }        
        return $form;        
    }

    // ページネートの値はデバイス＞変更可能
    public function getPainatecnt() {
        return 15;
    }

}
