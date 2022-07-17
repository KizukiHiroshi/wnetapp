<?php
declare(strict_types=1);
namespace App\Services\Database;

class GetWhereService 
{
    // $requestから検索要素を抽出する
    // 'string'は like
    public function getWhere($searchinput, $columnsprop) {
        $where =[];
        if ($searchinput) {
            // 文字の検索
            foreach ($columnsprop as $columnname => $prop) {
                if (array_key_exists($columnname, $searchinput)
                    && $searchinput[$columnname] !== null) {
                    if ($prop['type'] == 'string') {
                        // ' ' スペース検索でAND検索（半角に直しておく）
                        $words = str_replace('　', ' ', $searchinput[$columnname]);;
                        // ' ' スペース毎に%を補完する
                        $wordsarray = explode(' ', $words);
                        $words = '';
                        foreach ($wordsarray as $word) {
                            $words .= '%' . addcslashes($word, '%_\\') . '% ';
                        }
                        $words = substr($words, 0, strlen($words)-1);
                        // '^' キャレット検索でOR検索
                        $words = explode('^', $words);
                        $values = [];
                        foreach ($words as $word) {
                            $word = substr($word,0,1) == '%' ? $word : '%'.$word;
                            $word = substr($word,-1) == '%' ? $word : $word.'%';
                            $values[] = $word;
                        }
                        $where[$prop['tablename'].'.'.$prop['realcolumn']] = $values;
                    } else {
                        $where[$prop['tablename'].'.'.$prop['realcolumn']] = [$searchinput[$columnname]];
                    }
                }
            }
            // 数値、日付の範囲検索
            foreach ($columnsprop as $columnname => $prop) {
                $bigin = 'bigin_'.$columnname;
                $end = 'end_'.$columnname;
                if (array_key_exists($bigin, $searchinput)
                    && $searchinput[$bigin] !== null) {
                    $value = '>=|'.$searchinput[$bigin];        // '|'は不等式と値の間のキャラクター
                    if (array_key_exists($end, $searchinput)
                        && $searchinput[$end] !== null) {
                        $value  .= ' <=|'.$searchinput[$end];   // 先頭のスペースがアンド検索要素
                    }
                    $where[$prop['tablename'].'.'.$columnname] = [$value];
                } elseif (array_key_exists($end, $searchinput)
                    && $searchinput[$end] !== null) {
                    $value = '<=|'.$searchinput[$end];              // '|'は不等式と値の間のキャラクター
                    if (array_key_exists($bigin, $searchinput)
                        && $searchinput[$bigin] !== null) {
                        $value  .= ' >=|'.$searchinput[$bigin];     // 先頭のスペースがアンド検索要素
                    }
                    $where[$prop['tablename'].'.'.$columnname] = [$value];
                }
            }
        }
        return $where;
    }
}