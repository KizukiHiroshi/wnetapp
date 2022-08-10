<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// データ実態を取得るためのQueryを取得する

declare(strict_types=1);
namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\SessionService;
use App\Services\Database\GetNoHeadernameService;

class QueryService 
{
    // queryのfrom,join,select句を取得する
    public function getTableQuery($request, $displaymode, $tempsort = null) {
        $tablename= $request->tablename;$sessionservice = new SessionService;
        $searchconditions = $sessionservice->getSession('searchconditions');
        $columnsprop = $sessionservice->getSession('columnsprop');
        $modelindex = $sessionservice->getSession('modelindex');
        $getwhereservice = new GetWhereService;
        $where = $getwhereservice->getWhere($searchconditions, $columnsprop);
        $modelname = $modelindex[$tablename]['modelname'];
        // Trashの扱い
        if ($displaymode == 'card') {
            $tablequery = $modelname::withTrashed();
        } elseif (isset($searchconditions['trashed'])) {   // 検索条件から
            if ($searchconditions['trashed'] == 'with') {
                $tablequery = $modelname::withTrashed();
            } elseif ($searchconditions['trashed'] == 'only') {
                $tablequery = $modelname::onlyTrashed();
            } else {
                $tablequery = $modelname::query();
            }
        } else {
            $tablequery = $modelname::query();
        }
        // from句
        $tablequery = $tablequery->from($tablename);
        // ForeignId用join句
        $tablequery = $this->addIdJoinToQuery($tablequery, $tablename, $columnsprop);
        // _opt用join句
        $tablequery = $this->addOptJoinToQuery($tablequery, $tablename, $columnsprop);
        // select句
        $selectclause = $this->setSelectClauseForDisplaymode($columnsprop, $displaymode);
        $tablequery = $tablequery->select(DB::raw($selectclause));
        // where句
        if ($where) {$this->setWhereclause($tablequery, $where);}
        // order句
        if ($displaymode == 'list' && $tempsort) {
            $rawText = $this->changeSortarrayToRawtext($tempsort);
            $tablequery = $tablequery->orderByRaw($rawText);
        }
        return $tablequery;
    }

    // $tablequeryに$whereclauseを追加する
    private function setWhereclause($tablequery, $where) {
        foreach ($where as $columnname => $values) {
            if (count($values) == 1) {
                $is_or = false; 
                $value = $values[0];
                if (strpos($value, ' ') !== false) {    // 同じカラムのAND要素
                    $subvalues = explode(' ', $value);
                    foreach ($subvalues as $subvalue) {
                        $this->addWhereToQuery($tablequery, $is_or, $columnname, $subvalue);
                    }
                } else {
                    $this->addWhereToQuery($tablequery, $is_or, $columnname, $value);
                }
            } else {    // 同じカラムのOR要素
                $tablequery = $tablequery->where(function($query) use($columnname, $values) {
                    foreach ($values as $value) {
                        $is_or = true;
                        $this->addWhereToQuery($query, $is_or, $columnname, $value);
                    }
                    return $query;
                });
            }
        }
        return $tablequery;
    }

    // whereを実際に加える
    private function addWhereToQuery($query, $is_or, $columnname, $value) {
        $subvalues = explode(' ', $value);
        foreach ($subvalues as $subvalue) {
            if (substr($subvalue, 0, 1) == '%') {
                $inequality = 'like';
            } elseif (strpos($subvalue, '|') !== false) {
                $inequality = substr($subvalue, 0, strpos($subvalue, '|'));
                $subvalue =substr($subvalue, strpos($subvalue, '|')+1);
            } else {
                $inequality = '=';
            }
            if ($is_or) {
                $query = $query->orWhere($columnname, $inequality, $subvalue);
            } else {
                $query = $query->where($columnname, $inequality, $subvalue);
            }
        }
        return $query;
    }

    // tablequeryにForeinId用のjoin句を足す
    private function addIdJoinToQuery($tablequery, $tablename, $columnsprop) {
        // '〇_id'と参照の深さを得る
        $foreignkeys = [];
        foreach ($columnsprop as $columnname => $poroperty) {
            if (substr($columnname, -3) == '_id' && strpos($columnname, '_id_2nd_') == false) {
                $foreignkeys[$columnname] = substr_count($columnname, '_id');
            }
        }
        // 参照の浅い順に並べ替える
        asort($foreignkeys);
        // 同じテーブルをJOINする際にエイリアスを作る
        $foreigntablenames = [];
        // join句にして追加する（$valueは'_id'の数)
        $gettnoheadernameservice = new GetNoHeadernameService;
        foreach ($foreignkeys as $foreignkey => $value) {
            $noheaderforeignkey = $gettnoheadernameservice->getNoHeadername($foreignkey);
            $sourcetablename = '';  // 参照元テーブル名
            $sourcecolumnname ='';  // 参照元カラム;
            $foreigntablename = ''; // 参照先テーブル名
            if ($value == 1) {  // '_id_'が含まれていない
                $sourcetablename = $tablename;
                $sourcecolumnname = $foreignkey;
                $foreigntablename = Str::plural(substr($noheaderforeignkey, 0, -3));
            } else {
                // 後ろから2つ目のテーブル名
                // 一番後ろを消す
                $sourcetablename = substr($noheaderforeignkey, 0, strrpos($noheaderforeignkey, '_id_'));
                // 前に残っていればそれも消す
                if (strrpos($sourcetablename, '_id_')) {
                    $sourcetablename = substr($sourcetablename, strrpos($sourcetablename, '_id_') +4);
                }
                $sourcetablename = Str::plural($sourcetablename);
                // 一番後ろの'〇_id'
                $sourcecolumnname = substr($foreignkey, strrpos($foreignkey, '_id_') +4);
                // 一番後ろのテーブル名
                $foreigntablename = Str::plural(substr($sourcecolumnname, 0, -3));    

            }
            $jointablename = $foreigntablename;
            $jointableclauce = $foreigntablename;
            if (array_key_exists($foreigntablename, $foreigntablenames)) {
                $foreigntablenames[$foreigntablename] += 1;
                $cnt = $foreigntablenames[$foreigntablename];
                $jointablename = $foreigntablename.strval($cnt);
                $jointableclauce = $foreigntablename.' AS '.$jointablename;
            } else {
                $foreigntablenames[$foreigntablename] = 0;
            }
            $tablequery = $tablequery
                ->join($jointableclauce, $sourcetablename.'.'.$sourcecolumnname,'=', $jointablename.'.id');
        }
        return $tablequery;
    }

    // tablequeryに_opt用のjoin句を足す
    private function addOptJoinToQuery($tablequery, $tablename, $columnsprop) {
        // optを探す
        $optionkeys = [];
        foreach ($columnsprop as $columnname => $poroperty) {
            if (substr($columnname, -4) == '_opt') {
                $optionkeys[] = $columnname;
            }
        }
        $optiontablename = 'option_choices';
        // join句にして追加する
        foreach ($optionkeys as $optionkey) {
            $tablequery = $tablequery
                ->join($optiontablename. ' AS '.$optionkey.'ion', $tablename.'.'.$optionkey,'=', $optionkey.'ion'.".id");
        }
        return $tablequery;
    }

    // list,card表示に合わせてselect句を作る
    private function setSelectClauseForDisplaymode($columnsprop, $displaymode) {
        $selectclausearray = [];
        if ($displaymode == 'list') {
            foreach ($columnsprop AS $columnname => $prop) {
                if (substr($columnname, -4) == '_opt') {
                    $selectclausearray[]= $columnname.'ion.valuename as '.$columnname;
                } elseif (strpos($columnname, '_id_') == false) {
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
        $concatclause .= "'".$referencedcolumnname."'";
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