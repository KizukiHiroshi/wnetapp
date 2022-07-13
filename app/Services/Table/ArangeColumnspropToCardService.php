<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Modelから取得したデータを整理する

declare(strict_types=1);
namespace App\Services\Table;

class ArangeColumnspropToCardService {

    public function __construct() {
    }

    // columnspropのforeignをcard表示用に変更する
    public function arangeColumnspropToCard($columnsprop) {
        $cardcolumnsprop = [];
        $foreigncolumn = '';
        // $referencecolumns = $this->getReferenceColumns($columnsprop);
        foreach ($columnsprop AS $columnname => $prop) {
            if (substr($columnname, -4) == '_opt') {
                $cardcolumnsprop[$columnname] = $prop;
                $prop['type'] = 'string';
                $cardcolumnsprop[$columnname.'_reference'] = $prop;
            } elseif (strpos($columnname, '_id_') == false || substr($columnname, -3) == '_id' || substr($columnname, -7) == '_id_2nd') {
                $cardcolumnsprop[$columnname] = $prop;
                if (substr($columnname, -3) == '_id' || substr($columnname, -7) == '_id_2nd') {
                    $foreigncolumn = $columnname;
                    // foreign_idの後にreferenceを入れる
                    $prop['type'] = 'string';
                    $cardcolumnsprop[$foreigncolumn.'_reference'] = $prop;
                }
            } elseif (strpos($columnname, $foreigncolumn) !== false && strpos($columnname, '_id_') !== false) {
                if (intval($prop['length']) > 0) {
                    $length = intval($cardcolumnsprop[$foreigncolumn.'_reference']['length']);
                    $length +=intval($prop['length']);
                    $cardcolumnsprop[$foreigncolumn.'_reference']['length'] = strval($length);
                }
            }
        }
        return $cardcolumnsprop;
    }
}
