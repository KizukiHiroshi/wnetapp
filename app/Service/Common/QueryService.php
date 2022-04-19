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
        $where = $this->getWhere($request, $columnsprop);
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
        // where句
        if ($where) {$this->setWhereclause($tablequery, $where);}
        // order句
        if ($tempsort) {
            $rawText = $this->changeSortarrayToRawtext($tempsort);
            $tablequery = $tablequery->orderByRaw($rawText);
        }
        return $tablequery;
    }

    // $tablequeryに$whereclauseを追加する
    private function setWhereclause($tablequery, $where) {
         foreach ($where as $columnsname => $values) {
            if (count($values) == 1) {
                $value = $values[0];
                if (substr($value, 0, 1) == '%') {
                    $tablequery = $tablequery->where($columnsname, 'like', $value);
                } else {
                    $tablequery = $tablequery->where($columnsname, '=', $value);
                }
            } else {
                $tablequery = $tablequery->where(function($query) use($columnsname, $values){
                    foreach ($values as $value) {
                        if (substr($value, 0, 1) == '%') {
                            $query = $query->orWhere($columnsname, 'like', $value);
                        } else {
                            $query = $query->orWhere($columnsname, '=', $value);
                        }
                    }
                    return $query;
                });
            }
        }
        return $tablequery;
    }

    // $requestから検索要素を抽出する
    // 'string'は like
    private function getWhere($request, $columnsprop) {
        $where =[];
        foreach ($columnsprop as $columnsname => $prop) {
            if ($request->$columnsname) {
                if ($prop['type'] == 'string') {
                    $words = str_replace('　', ' ', $request->$columnsname);;
                    $words = explode(' ', $words);
                    $values = [];
                    foreach ($words as $word) {
                        $values[] = '%' . addcslashes($word, '%_\\') . '%';
                    }
                    $where[$prop['tablename'].'.'.$columnsname] = $values;
                } elseif ($prop['type'] == 'bigint' || $prop['type'] == 'boolean') {
                    $where[$prop['tablename'].'.'.$columnsname] = [$request->$columnsname];
                }
            }
        }
        return $where;
    }

    // tablequeryにjoin句を足す
    private function addJoinToQuery($tablequery, $tablename, $columnsprop) {
        foreach ($columnsprop as $columnname => $poroperty) {
            if (substr($columnname, -3) == '_id') {
                if (strpos($columnname, '_id_') == false) {
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
                if (strpos($columnname, '_id_') == false) {
                    $selectclausearray[]= $prop['tablename'].'.'.$prop['realcolumn'];
                } else {
                    $selectclausearray[] = $prop['tablename'].'.'.$prop['realcolumn'].' as '.$columnname;
                }
            }
        } elseif ($displaymode == 'card') {
            // card表示用に参照カラムをまとめる
            $foreignconcat = $this->getForeignConcat($columnsprop);
            foreach ($columnsprop AS $columnname => $prop) {
                if (strpos($columnname, '_id_') == false) {
                    $selectclausearray[] = $prop['tablename'].'.'.$prop['realcolumn'];
                    if (strpos($columnname, '_id') !== false) {
                        $selectclausearray[] = $foreignconcat[$columnname];
                    }
                } elseif (strpos($columnname, '_id_') !== false) {
                    // 参照カラムは_idの後にまとめて登録するので何もしない
                }
            }
        }
        $selectclause = implode(', ', $selectclausearray);
        // dd($selectclause);
        return $selectclause;
    }

    // card表示用に参照カラムをconcatにまとめる
    // $foreignvoncat = [
    //      カラム名 => 参照先テーブル.参照先カラム AS カラム名,
    //      カラム名 => CONCAT_WS(参照先テーブル.参照先カラム, 参照先テーブル.参照先カラム) AS 表示カラム名
    // ]
    private function getForeignConcat($columnsprop) {
        $foreignconcat = [];
        $concats = [];
        // 参照元カラム名を取得
        foreach ($columnsprop AS $columnname => $prop) {
            if (substr($columnname,-3) == '_id') {
                $concats[$columnname] = [];
            }
        }
        // 参照先カラムを取得
        foreach ($columnsprop AS $columnname => $prop) {
            if (strpos($columnname, '_id_') > 0) {
                $foreigncolumn = Str::before($columnname, '_id_').'_id';
                $concats[$foreigncolumn][] = $prop['tablename'].'.'.$prop['realcolumn'];
            }
        }
        // selectclauceに使える様に加工
        foreach ($concats AS $foreigncolumn => $concat) {
            if (count($concat) == 1) {
                $foreignconcat[$foreigncolumn] = $concat[0].' as '.$foreigncolumn;
            } else {
                $forerignreferencename = substr($foreigncolumn,0,-3).'_reference';
                $foreignconcat[$foreigncolumn] 
                = $this->getConcatClause($concat, ' ', $forerignreferencename);
            }
        }
        return $foreignconcat;
    }

    // CONCAT_WS()句を作成する
    // $concat = [参照先テーブル.参照先カラム名, 参照先テーブル.参照先カラム名, ...]
    // $joinchar:表示の際に値を繋ぐ文字
    // $referencedcolumnname:表示の際に使うカラム名
    public function getConcatClause($concat, $joinchar, $referencedcolumnname) {
        $concatclause = "CONCAT_WS('".$joinchar."', ";
        foreach ($concat AS $column) {
            $concatclause .= $column.", ";
        }
        $concatclause = rtrim($concatclause, ", ");
        $concatclause .= ") as ";
        $concatclause .= $referencedcolumnname;
        return $concatclause;
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

}