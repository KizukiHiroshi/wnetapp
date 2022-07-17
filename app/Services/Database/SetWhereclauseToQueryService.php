<?php
declare(strict_types=1);
namespace App\Services\Database;

class SetWhereclauseToQueryService 
{
    // $tablequeryに$whereclauseを追加する
    public function setWhereclauseToQuery($tablequery, $where) {
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
}