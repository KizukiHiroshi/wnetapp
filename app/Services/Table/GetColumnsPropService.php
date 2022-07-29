<?php

declare(strict_types=1);
namespace App\Services\Table;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Database\GetNoHeaderColumnnameService;
use App\Services\Model\GetModelIndexService;

class GetColumnsPropService {

    public function __construct() {
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
    public function getColumnsProp($tablename) {
        $modelindexservice = new GetModelIndexService;
        $modelindex = $modelindexservice->getModelindex();
        $columns = DB::select('show full columns from '.$tablename);
        $columnsprop = [];
        // テーブルのuniquekey取得
        $uniquekeys = $this->getUniquekeys($modelindex, $tablename);
        foreach ($columns as $column) {
            $columnname = $column->Field;
            $sortcolumn = $columnname;
            $isunique = in_array($columnname, $uniquekeys) ? TRUE : NULL;
            // foreign_idの場合
            if (substr($columnname,-3) == '_id' || substr($columnname,-7) == '_id_2nd') {
                $gettnoheadercolumnnameservice = new GetNoHeaderColumnnameService;
                $noheadercolumnname = $gettnoheadercolumnnameservice->getNoHeaderColumnname($columnname);
                $refcolumnsprop = [];
                // 参照キー(〇〇_id)の参照先を$columnspropに入れる再帰関数
                $refcolumnsprop = $this->delveId($refcolumnsprop, $modelindex, $tablename, $columnname, $noheadercolumnname);
                // 参照値のnotnull値を管理する
                $refcolumnsprop = $this->setNotnullgToRefcolumnsprop($refcolumnsprop);
                // $refcolumnspropを参照の深い順に並べ替える
                $refcolumnsprop = $this->sortRefcolumnsporp($refcolumnsprop);
                $columnsprop = array_merge($columnsprop, $refcolumnsprop);
            } else {
                $columnsprop[$columnname] = $this->getColumnProp($tablename, $columnname, $sortcolumn, $isunique);
            }
        }
        return $columnsprop;
    }

    // テーブルのuniquekey取得
    private function getUniquekeys($modelindex, $tablename) {
        $model = $modelindex[$tablename];
        $uniquekeys = $model['modelname']::$uniquekeys;
        $uniquekeystr = '';
        foreach ($uniquekeys as $key => $uniquekey) {
            $uniquekeystr .= implode(',', $uniquekey);
        }
        $uniquekeys = explode(',', $uniquekeystr);
        return $uniquekeys;
    }


    // 参照値のnotnull値を管理する
    private function setNotnullgToRefcolumnsprop($refcolumnsprop) {
        foreach ($refcolumnsprop as $columnname => $prop) {
            if (substr($columnname,-3) == '_id') {
                // _idには何もしない
            } else {
                if (strpos($columnname, '_2nd') !== false) {
                    // _2nd要素の参照は全てnotnull=false
                    $refcolumnsprop[$columnname]['isunique'] = false;
                    $refcolumnsprop[$columnname]['notnull'] = false;
                } else {
                    // 参照元でnotonullであってもunique以外のものは、notnull=falseとする
                    if ($refcolumnsprop[$columnname]['isunique'] == null) {
                        $refcolumnsprop[$columnname]['notnull'] = false;
                    }
                }
            }
        }
        return $refcolumnsprop;
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
    private function delveId ($refcolumnsprop, $modelindex, $tablename, $columnname, $noheadercolumnname) {
        $uniquekeys = $this->getUniquekeys($modelindex, $tablename);
        // '_id_'が含まれていればそこまで消す
        if (strripos($noheadercolumnname, '_id_') && substr($noheadercolumnname,-7) !== '_id_2nd') {
            if (strripos($noheadercolumnname, '_id_2nd_')) {
                $realcolumnname = substr($noheadercolumnname, strripos($noheadercolumnname, '_id_2nd_') + 8);
            } else {
                $realcolumnname = substr($noheadercolumnname, strripos($noheadercolumnname, '_id_') + 4);
            }
        } else {
            $isunique = in_array($columnname, $uniquekeys) ? TRUE : NULL;
            $refcolumnsprop[$columnname] = $this->getColumnProp($tablename, $columnname, $columnname, $isunique);
            $realcolumnname = $noheadercolumnname ;
        }
        $foreigntablename = Str::plural(Str::before($realcolumnname, '_id'));
        $foreignuniquekeys = $this->getUniquekeys($modelindex, $foreigntablename);
        $foreignmodel = $modelindex[$foreigntablename];
        $referencedcolumnnames = $foreignmodel['modelname']::$referencedcolumns;
        $refcolumnname = '';
        foreach($referencedcolumnnames AS $referencedcolumnname) {
            $referencedsortcolumnname
                = $this->checkAlternativeSortColumn($referencedcolumnname, $foreigntablename);
            $isunique = in_array($referencedcolumnname, $foreignuniquekeys) ? TRUE : NULL;
            $newprop = [$columnname.'_'.$referencedcolumnname =>
            $this->getColumnProp($foreigntablename, $referencedcolumnname, $referencedsortcolumnname, $isunique)];
            $refcolumnsprop = array_merge($refcolumnsprop, $newprop);
            if (substr($referencedcolumnname,-3) == '_id') {
                $refcolumnname = $columnname.'_'.$referencedcolumnname;
            }
        }
        if ($refcolumnname == '') {
            return $refcolumnsprop;
        } else {
            $gettnoheadercolumnnameservice = new GetNoHeaderColumnnameService;
            $noheadercolumnname = $gettnoheadercolumnnameservice->getNoHeaderColumnname($refcolumnname);
            return $this->delveId ($refcolumnsprop, $modelindex, $foreigntablename, $refcolumnname, $noheadercolumnname);
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


}
