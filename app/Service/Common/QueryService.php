<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// データ実態を取得るためのQueryを取得する

declare(strict_types=1);
namespace App\Service\Common;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueryService 
{
    // queryのfrom,join,select句を取得する
    public function getTableQuery($request, $modelindex, $columnsprop, $displaymode, $tempsort = null) {
        $tablename= $request->tablename;
        $where = $request->where;
        $group = $request->group;
        $modelname = $modelindex[$tablename]['modelname'];
        // Trashの扱い
        if ($displaymode == 'card') {
            $tablequery = $modelname::withTrashed();
        } else {
            $tablequery = $modelname::query();
        }
        // from句
        $tablequery = $tablequery->from($tablename);
        // join句
        $tablequery = $this->addJoinToQuery($tablequery, $tablename, $columnsprop);
        // select句
        $selectclause = $this->setSelectClauseForDisplaymode($columnsprop, $displaymode);
        $tablequery = $tablequery->select(DB::raw($selectclause));
        // order句
        if ($tempsort) {
            $rawText = $this->changeSortarrayToRawtext($tempsort);
            $tablequery = $tablequery->orderByRaw($rawText);
        }
        return $tablequery;
    }

    // tablequeryにjoin句を足す
    private function addJoinToQuery($tablequery, $tablename, $columnsprop) {
        foreach ($columnsprop as $columnname => $poroperty) {
            if (substr($columnname, -3) == '_id') {
                if (strpos($columnname, '_id_')===false) {
                    $foreigntablename = Str::plural(substr($columnname, 0, -3));
                    $tablequery = $tablequery
                        ->join($foreigntablename, $tablename.'.'.$columnname,'=', $foreigntablename.".id");
                } else {
                    $foreigntablename = Str::plural(substr($columnname, 0, strpos($columnname, '_id_')));
                    $deepforeigncolumnname = substr($columnname, strpos($columnname, '_id_') +4);
                    $deepforeigntablename = Str::plural(substr($deepforeigncolumnname, 0, -3));
                    $tablequery = $tablequery
                        ->join($deepforeigntablename, 
                            $foreigntablename.".".$deepforeigncolumnname,'=', $deepforeigntablename.".id");
                }
            }
        }
        return $tablequery;
    }

    // list,card表示に合わせてselect句を作る
    private function setSelectClauseForDisplaymode($columnsprop, $displaymode) {
        $selectclausearray = [];
        if ($displaymode == 'list') {
            foreach ($columnsprop AS $columnname => $prop) {
                if (strpos($columnname, '_id_')===false) {
                    $selectclausearray[]= $prop['tablename'].'.'.$prop['realcolumn'];
                } else {
                    $selectclausearray[] = $prop['tablename'].'.'.$prop['realcolumn'].' as '.$columnname;
                }
            }
        } elseif ($displaymode == 'card') {
            // card表示用に参照カラムをまとめる
            $foreignconcat = $this->getForeignConcat($columnsprop);
            foreach ($columnsprop AS $columnname => $prop) {
                if (strpos($columnname, '_id_')===false) {
                    $selectclausearray[] = $prop['tablename'].'.'.$prop['realcolumn'];
                    if (strpos($columnname, '_id')!==false) {
                        $selectclausearray[] = $foreignconcat[$columnname];
                    }
                } elseif (strpos($columnname, '_id_')!==false) {
                    // 参照カラムは_idの後にまとめて登録するので何もしない
                }
            }
        }
        $selectclause = implode(', ', $selectclausearray);
        // dd($selectclause);
        return $selectclause;
    }

    // card表示用に参照カラムをconcatにまとめる
    private function getForeignConcat($columnsprop) {
        $foreignconcat = [];
        $foreigncolumn = '';
        $concats = [];
        foreach ($columnsprop AS $columnname => $prop) {
            if (substr($columnname,-3)=='_id') {
                $foreigncolumn = $columnname;
            } elseif ($foreigncolumn!='') {
                if (strpos($columnname, $foreigncolumn)!==false && strpos($columnname, '_id_')!==false) {
                    $concats[] = $prop['tablename'].'.'.$prop['realcolumn'];
                } else {
                    if (count($concats)==1) {
                        $foreignconcat[$foreigncolumn] = $concats[0].' as '.$foreigncolumn;
                    } else {
                        $forerignreferencename = substr($foreigncolumn,0,-3).'_reference';
                        $foreignconcat[$foreigncolumn] 
                        = $this->getConcatClasuse($concats, ' ', $forerignreferencename);
                    }
                    $foreigncolumn = '';
                    $concats = [];
                }
            }
        }
        return $foreignconcat;
    }

    // Sort条件の配列を、orderByRaw句に使えるテキストに変える
    private function changeSortarrayToRawtext($sortarray) {
        $sortText = '';
        if (isset($sortarray)) {
        foreach($sortarray as $key => $value) {
            $sortText .= $key .' '. $value .', ';
        }
        $sortText = rtrim($sortText, ", ");
        } else {
            $sortText = NULL;
        }
        return $sortText;
    }

    // CONCAT_WS()句を作成するする
    public function getConcatClasuse($concats, $joinchar, $referencedcolumnname) {
        $concatclause = "CONCAT_WS('".$joinchar."', ";
        foreach ($concats AS $column) {
            $concatclause .= $column.", ";
        }
        $concatclause = rtrim($concatclause, ", ");
        $concatclause .= ") as ";
        $concatclause .= $referencedcolumnname;
        return $concatclause;
    }

}