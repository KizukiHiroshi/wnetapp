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
        // $columnnames = Schema::getColumnListing($tablename);
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
            $columnsprop[$columnname] = $this->getColumnProp($tablename, $columnname, $sortcolumn, $isunique);
            // foreign_idの場合
            if (substr($columnname,-3) == '_id') {
                $foreigntablename = Str::plural(substr($columnname, 0, -3));
                $foreignmodel = $modelindex[$foreigntablename];
                // 参照カラムを取得してプロパティに加える
                $referencedcolumnnames = $foreignmodel['modelname']::$referencedcolumns;
                foreach($referencedcolumnnames AS $referencedcolumnname) {
                    $referencedsortcolumnname = $this->checkAlternativeSortColumn($referencedcolumnname, $foreigntablename);
                    $columnsprop[$columnname.'_'.$referencedcolumnname] = 
                        $this->getColumnProp($foreigntablename, $referencedcolumnname, $referencedsortcolumnname);
                    // 参照の参照は2回までとする
                    if (substr($referencedcolumnname,-3) == '_id') {
                        $deepforeigntablename = Str::plural(substr($referencedcolumnname, 0, -3));
                        $deepforeignmodel = $modelindex[$deepforeigntablename];
                        $deepreferencedcolumnnames = $deepforeignmodel['modelname']::$referencedcolumns;
                        foreach($deepreferencedcolumnnames AS $deepreferencedcolumnname) {
                            $deepreferencedsortcolumnname 
                                = $this->checkAlternativeSortColumn($deepreferencedcolumnname, $deepforeigntablename);
                            $columnsprop[$columnname.'_'.$referencedcolumnname.'_'.$deepreferencedsortcolumnname] = 
                                $this->getColumnProp($deepforeigntablename, $deepreferencedcolumnname, $deepreferencedsortcolumnname);
                        } 
                    }                                   
                } 
            }
        }
        return $columnsprop;
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
            if (strpos($columnname, '_id_') == false) {
                $cardcolumnsprop[$columnname] = $prop;
                if (strpos($columnname, '_id') !== false) {
                    $foreigncolumn = $columnname;
                    // foreign_idの後にreferenceを入れる
                    $prop['type'] = 'string';
                    $cardcolumnsprop[substr($columnname, 0, -3).'_reference'] = $prop;
                }
            } elseif (strpos($columnname, $foreigncolumn) !== false && strpos($columnname, '_id_') !== false) {
                if (intval($prop['length']) > 0) {
                    $length = intval($cardcolumnsprop[substr($foreigncolumn, 0, -3).'_reference']['length']);
                    $length +=intval($prop['length']);
                    $cardcolumnsprop[substr($foreigncolumn, 0, -3).'_reference']['length'] = strval($length);
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
