<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Modelから取得したデータを整理する

declare(strict_types=1);
namespace App\Services\Table;

class GetEmptyRowService {

    public function __construct(){
    }

    // 表示する行の空データを作る
    public function getEmptyRow($columnsprop){
        $rawrow =[];
        foreach ($columnsprop AS $columnname => $value){
            $rawrow[$columnname] = 'old('.$columnname.')';
        }
        $row = (object) $rawrow;
        return $row;
    }
}
