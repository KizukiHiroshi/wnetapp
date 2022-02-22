<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// 汎用性のある関数を登録する

declare(strict_types=1);
namespace App\Service\Common;

class CommonService {

    // 新規配列の内、既知のKeyで既存配列に存在しないKeyValueを既存配列に追加する
    public function addArrayIfknownsKeyAndNotExist($newarray, $temparray, $knownkeys) {
        foreach ($newarray as $key => $value) {
            if (!array_key_exists($key, $temparray) && in_array($key, $knownkeys)) {
                $temparray[$key] = $value;
            }
        }
        return $temparray;
    }


}