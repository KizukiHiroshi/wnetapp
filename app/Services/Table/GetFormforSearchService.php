<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Modelから取得したデータを整理する

declare(strict_types=1);
namespace App\Services\Table;

class GetFormforSearchService {

    public function __construct(){
    }

    // searchで入力されたbigin_,end_ヘッダー付検索条件を、
    // validationの対象にするために元のカラム名に戻す
    public function getFormforSearch($withhearder, $columnsprop, $searchinput){
        $form = [];
        foreach ($columnsprop as $columnname => $value){
            if (array_key_exists($withhearder.$columnname, $searchinput)){
                $form[$columnname] = $searchinput[$withhearder.$columnname];
            }
        }
        return $form;
    }
}
