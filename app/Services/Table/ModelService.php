<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Modelから取得したデータを整理する

declare(strict_types=1);
namespace App\Services\Table;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\Database\Get_ByNameService;
use App\Services\Database\Add_ByNameToFormService;


class ModelService {

    private $get_bynameservice;
    private $add_bynametoformservice;
    public function __construct(
        Get_ByNameService $get_bynameservice,
        Add_ByNameToFormService $add_bynametoformservice
    ) {
        $this->add_bynametoformservice = $add_bynametoformservice;
        $this->get_bynameservice = $get_bynameservice;
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
    
    // columnspropのforeignをcard表示用に変更する
    public function arangeColumnspropToCard($columnsprop) {
        $cardcolumnsprop = [];
        $foreigncolumn = '';
        foreach ($columnsprop AS $columnname => $prop) {
            if (strpos($columnname, '_id_') == false || substr($columnname, -3) =='_id' || substr($columnname, -7) =='_id_2nd') {
                $cardcolumnsprop[$columnname] = $prop;
                if (substr($columnname, -3) =='_id' || substr($columnname, -7) =='_id_2nd') {
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
    
    // 表示する行の空データを作る
    public function getEmptyRow($columnsprop) {
        $rawrow =[];
        foreach ($columnsprop AS $columnname => $value) {
            $rawrow[$columnname] = 'old('.$columnname.')';
        }
        $row = (object) $rawrow;
        return $row;
    }
}
