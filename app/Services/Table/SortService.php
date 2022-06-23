<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Listのソート情報を管理する

declare(strict_types=1);
namespace App\Services\Table;

use App\Services\SessionService;
use Illuminate\Support\Str;

class SortService
{
    public function __construct() {
    }

    /* tempsort:Listのソート順を取得する
    直近のソート要求＞タスク独自ソート＞テーブル既存ソート＞一般ソートの順に整理する
    $newsort(lastsort)>$tasksort>$defaultsort>$generalsort
    [テーブル名.カラム名 => asc or desc, ]
    */
    public function getTempsort($request, $modelindex, $columnsprop, $tasksort) {
        $tempsort =[];
        // 直近のソート要求を先頭にする
        $tempsort = $this->addNewsortToTempsort($request->newsort); // newsortは 'tablename_columnname'
        // Listに表示する全カラムを[テーブル名.コラム名,]の配列で取得する
        $allcolumns = $this->connectTablenameAndcolumns($columnsprop);
        // タスク用のソート順を追加する
        if ($tasksort) {
            $tempsort = $this->addTasksortToTempsort($tempsort, $tasksort, $allcolumns);
        }
        // テーブル既存のソート順を追加する
        $tempsort = $this->addDefaultsortToTempsort($tempsort, $request->tablename, $modelindex, $allcolumns);
        // 一般ソート順を追加する
        $tempsort = $this->addGeneralsortToTempsort($tempsort, $request->tablename, $modelindex, $allcolumns);
        return $tempsort;
    }

    // 直近のソート要求を先頭にする
    private function addNewsortToTempsort($newsort) {
        $tempsort = [];
        $sessionservice = new SessionService;
        $lastsort = $sessionservice->getSession('lastsort');
        if ($lastsort !== NULL) {            // 前回ソートのカラムがあれば内容を取得        
            $lastsortkey = substr($lastsort, 0, strpos($lastsort,'--'));
            $lastsortvalue = substr($lastsort, strpos($lastsort,'--')+2 );
        }
        // newsortは 'tablename_columnname'
        // 最初のソート項目を決定する
        if ($newsort !== NULL && $lastsort == NULL) {               // 初めて項目名をクリックした場合
            // 前回要求が無ければ今回要求の正順を作る
            $tempsort[$newsort] = "asc";
        } elseif ($newsort !== NULL && $lastsort !== NULL) {        // 2度目以後に項目名をクリックした場合
            if ($lastsortkey == $newsort) {
                // 前回と今回の要求カラムが同じなら逆ソート順を作る
                $newsortvalue = ($lastsortvalue == "asc" ? "desc" : "asc");
                $tempsort[$newsort] = $newsortvalue;
            } else {
                // 前回要求と違えば今回要求の正順を作る
                $tempsort[$newsort] = "asc";
            }            
        } elseif ($newsort == NULL && $lastsort !== NULL) {         // ペジネーションをクリックした場合
            // 今回要求が無く前回要求があれば、前回要求のまま作る
            $tempsort[$lastsortkey] = $lastsortvalue;
        }
        return $tempsort;
    }

    // タスク用のソート順を追加
    private function addTasksortToTempsort($tempsort, $tasksort, $showcolumns) {
        $newarray = $tasksort;
        $temparray = $tempsort;
        $knownkeys = $showcolumns;
        $tempsort = $this->addArrayIfknownsKeyAndNotExist($newarray, $temparray, $knownkeys);
        return $tempsort;
    }
    
    // $columnspropからテーブル名.コラム名の配列を作る
    private function connectTablenameAndcolumns($columnsprop) {
        $showcolumns = [];
        foreach ($columnsprop as $columnname => $prop) {
            if (strpos($columnname,'_id_') == false) {
                $showcolumns[] = $prop['tablename'].'.'.$columnname;
            } else {
                $showcolumns[] = $prop['tablename'].'.'.Str::after($columnname,'_id_');
            }
        }
        return $showcolumns;
    }
    
    // テーブル既存のソート順を追加する
    private function addDefaultsortToTempsort($tempsort, $tablename, $modelindex, $showcolumns) {
        // テーブル既存のソート順を取得する
        $defaultsort = $modelindex[$tablename]['modelname']::$defaultsort;
        // テーブル既存のソート順をTempsortのformに変える
        $defaultsort = $this->setDefaultsortToTempsortForm($tablename, $defaultsort, $modelindex);
        $newarray = $defaultsort;
        $temparray = $tempsort;
        $knownkeys = $showcolumns;
        $tempsort = $this->addArrayIfknownsKeyAndNotExist($newarray, $temparray, $knownkeys);
        return $tempsort;
    }

    // テーブル既存のソート順をTempsortのformに変える
    private function setDefaultsortToTempsortForm($tablename, $defaultsort, $modelindex) {
        $formedsort = [];
        foreach ($defaultsort as $columnname => $sortway) {
            if (substr($columnname, -3) !== '_id') {  // 通常のカラム
                $formedsort[$tablename.'.'.$columnname] = $sortway;
            } else {                                // 参照カラム
                $foreigntablename = Str::plural(substr($columnname, 0, -3));
                $referencedcolumns = $modelindex[$foreigntablename]['modelname']::$referencedcolumns;
                foreach ($referencedcolumns as $referencedcolumn) {
                    $formedsort[$foreigntablename.'.'.$referencedcolumn] = $sortway;
                }
            }
        }
        return $formedsort;
    }

    // 一般ソート順を追加する
    private function addGeneralsortToTempsort($tempsort, $tablename, $showcolumns) {
        // 一般ソート順を取得する
        $generalsort = json_decode(\App\Consts\DatabaseConst::GENERAL_SORT);
        // Generalsortのカラム名にテーブル名を足す
        $generalsort = $this->addTablenametoGeneralsort($tablename, $generalsort);
        $newarray = $generalsort;
        $temparray = $tempsort;
        $knownkeys = $showcolumns;
        $tempsort = $this->addArrayIfknownsKeyAndNotExist($newarray, $temparray, $knownkeys);
        return $tempsort;
    }

    // Generalsortのカラム名にテーブル名を足す
    private function addTablenametoGeneralsort($tablename, $generalsort) {
        $tempsort = [];
        foreach ($generalsort AS $column => $sortway) {
            $tempsort[$tablename.'.'.$column] = $sortway;
        }
        return $tempsort;
    }

    // 新規配列の内、既知のKeyで既存配列に存在しないKeyValueを既存配列に追加する
    private function addArrayIfknownsKeyAndNotExist($newarray, $temparray, $knownkeys) {
        foreach ($newarray as $key => $value) {
            if (!array_key_exists($key, $temparray) && in_array($key, $knownkeys)) {
                $temparray[$key] = $value;
            }
        }
        return $temparray;
    }
    
}