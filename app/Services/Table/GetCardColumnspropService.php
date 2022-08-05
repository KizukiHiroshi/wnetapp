<?php
declare(strict_types=1);
namespace App\Services\Table;

class GetCardColumnspropService {

    public function __construct() {
    }

    // columnspropにcard表示用のリファレンスを加える
    public function getCardColumnsprop($columnsprop) {
        $cardcolumnsprop = [];
        $foreigncolumn = '';
        $foreignprop =[];
        $findreference = false;
        foreach ($columnsprop AS $columnname => $prop) {
            if ($findreference && substr($columnname, 0, strlen($foreigncolumn)) <> $foreigncolumn) {
                // 参照用のカラムが終わったところに参照セレクトを入れる
                $cardcolumnsprop[$foreigncolumn.'_reference'] = $foreignprop;
                $findreference = false;
            }
            if (substr($columnname, -4) == '_opt') {
                // 数値選択カラム
                $cardcolumnsprop[$columnname] = $prop;
                $prop['type'] = 'string';
                $cardcolumnsprop[$columnname.'_reference'] = $prop;
            } elseif (strpos($columnname, '_id_') && substr($columnname, -2) !== 'id' && substr($columnname, -7) !== '_id_2nd') {
                // 参照用のカラム
                $cardcolumnsprop[$columnname] = $prop;
            } else {
                // 参照idカラムか普通のカラム
                $cardcolumnsprop[$columnname] = $prop;
                if (substr($columnname, -3) == '_id' || substr($columnname, -7) == '_id_2nd') {
                    // 参照セレクトのために参照idカラム名とpropを保存する
                    $findreference = true;
                    $foreigncolumn = $columnname;
                    $foreignprop = $prop;
                    $foreignprop['type'] = 'string';
            }
            } 
        }
        return $cardcolumnsprop;
    }
}
