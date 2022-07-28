<?php
declare(strict_types=1);
namespace App\Services\Table;

class ArangeColumnspropToCardService {

    public function __construct() {
    }

    // columnspropのforeignをcard表示用に変更する
    public function arangeColumnspropToCard($columnsprop) {
        $cardcolumnsprop = [];
        $foreigncolumn = '';
        $foreignprop =[];
        // $referencecolumns = $this->getReferenceColumns($columnsprop);
        $findreference = false;
        foreach ($columnsprop AS $columnname => $prop) {
            if ($findreference && strpos($columnname, '_id_') === false) {
                // 参照用のカラムが終わったところに参照セレクトを入れる
                $cardcolumnsprop[$foreigncolumn.'_reference'] = $foreignprop;
                $prop['type'] = 'string';
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
                $findreference = true;
            } else {
                // 参照idカラムか普通のカラム
                $cardcolumnsprop[$columnname] = $prop;
                if (substr($columnname, -3) == '_id' || substr($columnname, -7) == '_id_2nd') {
                    $foreigncolumn = $columnname;
                    $foreignprop = $prop;
                }
            } 
        }
        return $cardcolumnsprop;
    }
}
